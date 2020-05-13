<?php

namespace Schmitzal\Tinyimg\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

use Schmitzal\Tinyimg\Domain\Repository\FileRepository;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class CompressImageService
 * @package Schmitzal\Tinyimg\Service
 */
class CompressImageService
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * @var \Schmitzal\Tinyimg\Domain\Repository\FileRepository
     */
    protected $fileRepository = null;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
     */
    protected $persistenceManager = null;

    /**
     * @var array
     */
    protected $extConf = [];

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var S3Client
     */
    protected $client = null;

    /**
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager): void
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param FileRepository $fileRepository
     */
    public function injectFileRepository(FileRepository $fileRepository): void
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @param PersistenceManager $persistenceManager
     */
    public function injectPersistenceManager(PersistenceManager $persistenceManager): void
    {
        $this->persistenceManager = $persistenceManager;
    }

    /**
     * CompressImageService constructor.
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException
     * @throws \TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException
     */
    public function initAction(): void
    {
        if (version_compare(TYPO3_version, '9', '>')) {
            $this->extConf = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['tinyimg'];
        } else {
            // @extensionScannerIgnoreLine
            $this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['tinyimg']);
        }

        if (ExtensionManagementUtility::isLoaded('aus_driver_amazon_s3')) {
            $this->initCdn();
        }

        \Tinify\setKey($this->getApiKey());
        $this->settings = $this->getTypoScriptConfiguration();
    }

    /**
     * @return string
     */
    protected function getPublicPath(): string
    {
        if (version_compare(TYPO3_version, '9', '>')) {
            return Environment::getPublicPath() . '/';
        } else {
            // @extensionScannerIgnoreLine
            return PATH_site;
        }
    }

    /**
     * initialize the CDN
     */
    public function initCdn(): void
    {
        /** @var S3Client client */
        $this->client = S3Client::factory(array(
            'region' => $this->extConf['region'],
            'version' => $this->extConf['version'],
            'credentials' => array(
                'key' => $this->extConf['key'],
                'secret' => $this->extConf['secret'],
            ),
        ));
    }

    /**
     * @param File $file
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function initializeCompression($file): void
    {
        $this->initAction();

        if ($this->isFileInExcludeFolder($file)) {
            return;
        }

        if (!in_array(strtolower($file->getMimeType()), ['image/png', 'image/jpeg'], true)) {
            return;
        }

        if ((int)$this->settings['debug'] === 0) {
            try {
                $this->assureFileExists($file);
                $originalFileSize = $file->getSize();
                if ($this->checkForAmazonCdn($file)) {
                    $fileSize = $this->pushToTinyPngAndStoreToCdn($file);
                } else {
                    $publicUrl = $this->getPublicPath() . urldecode($file->getPublicUrl());
                    $source = \Tinify\fromFile($publicUrl);
                    $source->toFile($publicUrl);
                    $fileSize = $this->setCompressedForCurrentFile($file);
                }
                if ((int)$fileSize !== 0) {
                    $percentageSaved = (int)(100 - ((100 / $originalFileSize) * $fileSize));
                    $this->addMessageToFlashMessageQueue('success', [0 => (string)$percentageSaved . '%'], FlashMessage::INFO);
                }
                $this->updateFileInformation($file);
            } catch (\Exception $e) {
                $this->saveError($file, $e);
                $this->addMessageToFlashMessageQueue('compressionFailed', [0 => $e->getMessage()], FlashMessage::WARNING);
            }
        } else {
            $this->addMessageToFlashMessageQueue('debugMode', [], FlashMessage::INFO);
        }

    }

    /**
     * @param File $file
     * @throws \Exception
     */
    protected function assureFileExists(File $file): void
    {
        $absFileName = GeneralUtility::getFileAbsFileName(urldecode($file->getPublicUrl()));
        if (file_exists($absFileName) === false) {
            throw new \Exception('file not exists: ' . $absFileName, 1575270381);
        }
        if ((int)filesize($absFileName) === 0) {
            throw new \Exception('filesize is 0: ' . $absFileName, 1575270380);
        }
    }



    /**
     * @param File $file
     * @return bool
     */
    protected function isFileInExcludeFolder(File $file): bool
    {
        if (!empty($this->settings['excludeFolders'])) {
            $excludeFolders = GeneralUtility::trimExplode(',', $this->settings['excludeFolders'], true);
            $identifier = $file->getIdentifier();
            foreach ($excludeFolders as $excludeFolder) {
                if (strpos($identifier, $excludeFolder) === 0) {
                    $this->addMessageToFlashMessageQueue('folderExcluded', [0 => $excludeFolder], FlashMessage::INFO);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if the aus driver extension exists and is loaded.
     * Additionally it checks if CDN is actually set and
     * your located in the CDN section of the file list
     *
     * @param File $file
     * @return bool
     */
    public function checkForAmazonCdn(File $file): bool
    {
        return ExtensionManagementUtility::isLoaded('aus_driver_amazon_s3') &&
            $this->getUseCdn() &&
            $this->checkIfFolderIsCdn($file);
    }

    /**
     * Creates a temp file from original resource.
     * Pushes the temp image file to tinypng compression service.
     * Overrides the original temp file with the compressed on.
     * Puts the compressed temp image to the actual storeage in file list -> CDN
     * Deletes old temp file.
     *
     * @param File $file
     * @return int
     * @throws \Exception
     */
    public function pushToTinyPngAndStoreToCdn(File $file): int
    {
        // get the image
        // no PATH_site as file will be provided by absolute URL of the bucket or the CDN
        $publicUrl = $file->getPublicUrl();

        // get the temp file and prefix with current time
        $tempFile = $this->getPublicPath() . 'typo3temp' . DIRECTORY_SEPARATOR . time() . '_' . $this->getCdnFileName($publicUrl);

        $source = \Tinify\fromFile($publicUrl);

        // move to temp folder
        $source->toFile($tempFile);

        // upload to CDN
        $splFileObject = new \SplFileObject($tempFile);
        $fileSize = $splFileObject->getSize();
        $this->client->putObject([
            'Bucket' => $this->extConf['bucket'],
            'Key' => $file->getIdentifier(),
            'SourceFile' => $tempFile
        ]);
        // remove temp file
        GeneralUtility::unlink_tempfile($tempFile);
        return (int)$fileSize;
    }

    /**
     * This only works if file does not exist
     *
     * @param File $file
     * @return boolean
     */
    public function checkIfFolderIsCdn($file): bool
    {
        // if this is string, then we know, that there is already a file in the folder
        // In this case you have to check if the object in the bucket exists
        if (is_string($file->getParentFolder())) {
            return $this->client->doesObjectExist(
                $this->extConf['bucket'],
                $file->getIdentifier()
            );
        }

        return $file->getParentFolder()->getStorage()->getDriverType() === 'AusDriverAmazonS3';
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getCdnFileName(string $fileName): string
    {
        return preg_replace('/^.*\/(.*)$/', '$1', $fileName);
    }

    /**
     * @return string
     */
    protected function getApiKey(): string
    {
        return (string)$this->extConf['apiKey'];
    }

    /**
     * @return boolean
     */
    protected function getUseCdn(): bool
    {
        return (bool)$this->extConf['useCdn'];
    }

    /**
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getTypoScriptConfiguration(): array
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);

        return (array)$configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'tinyimg'
        );
    }

    /**
     * @param File $file
     */
    protected function updateFileInformation(File $file): void
    {
        /** @var Indexer $fileIndexer */
        $fileIndexer = $this->objectManager->get(Indexer::class, $file->getStorage());
        $fileIndexer->updateIndexEntry($file);
    }

    /**
     * @param File $file
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @return int
     */
    protected function setCompressedForCurrentFile(File $file): ?int
    {
        /** @var \Schmitzal\Tinyimg\Domain\Model\File $extbaseFileObject */
        $extbaseFileObject = $this->fileRepository->findByUid($file->getUid());
        $extbaseFileObject->setCompressed(true);
        $extbaseFileObject->resetCompressError();
        $this->fileRepository->update($extbaseFileObject);
        $this->persistenceManager->persistAll();
        try {
            $splFileObject = new \SplFileObject(GeneralUtility::getFileAbsFileName($file->getPublicUrl()));
            return (int)$splFileObject->getSize();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return bool
     */
    protected function isCli(): bool
    {
        if (version_compare(TYPO3_version, '9', '>')) {
            return Environment::isCli();
        } else {
            return php_sapi_name() === 'cli';
        }
    }


    /**
     * @param string $key
     * @param array $replaceMarkers
     * @param int $severity
     * @throws \TYPO3\CMS\Core\Exception
     */
    protected function addMessageToFlashMessageQueue($key, array $replaceMarkers = [], $severity = FlashMessage::ERROR): void
    {
        if ($this->isCli()) {
            return;
        }

        $localizationUtility = GeneralUtility::makeInstance(LocalizationUtility::class);
        $message = $localizationUtility->translate(
            'LLL:EXT:tinyimg/Resources/Private/Language/locallang.xlf:flashMessage.message.' . $key,
            null,
            $replaceMarkers
        );
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $localizationUtility->translate('LLL:EXT:tinyimg/Resources/Private/Language/locallang.xlf:flashMessage.title'),
            $severity,
            true
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * @param File $file
     * @param \Exception $e
     */
    protected function saveError(File $file, \Exception $e)
    {
        /** @var \Schmitzal\Tinyimg\Domain\Model\File $extbaseFileObject */
        $extbaseFileObject = $this->fileRepository->findByUid($file->getUid());
        $extbaseFileObject->setCompressed(false);
        $extbaseFileObject->setCompressError($e->getCode() . ' : ' . $e->getMessage());
        $this->fileRepository->update($extbaseFileObject);
        $this->persistenceManager->persistAll();
    }
}
