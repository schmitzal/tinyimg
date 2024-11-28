<?php

namespace Schmitzal\Tinyimg\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Schmitzal\Tinyimg\Domain\Repository\FileRepository;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class CompressImageService implements SingletonInterface
{
    protected array $extConf = [];
    protected ?S3Client $client = null;

    public function __construct(
        protected FileRepository $fileRepository,
        protected PersistenceManager $persistenceManager
    ) {
    }

    /**
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function initAction(): void
    {
        $this->extConf = (GeneralUtility::makeInstance(ExtensionConfiguration::class))->get('tinyimg');

        if (ExtensionManagementUtility::isLoaded('aus_driver_amazon_s3')) {
            $this->initCdn();
        }

        \Tinify\setKey($this->getApiKey());
    }

    public function initCdn(): void
    {
        /** @var S3Client client */
        $this->client = S3Client::factory(
            [
                'region' => $this->extConf['region'],
                'version' => $this->extConf['version'],
                'credentials' => [
                    'key' => $this->extConf['key'],
                    'secret' => $this->extConf['secret'],
                ],
            ]
        );
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
     */
    public function initializeCompression(File $file): void
    {
        $this->initAction();

        if ($this->isFileInExcludeFolder($file)) {
            return;
        }

        if (
            !in_array(
                strtolower($file->getMimeType()),
                [
                'image/png',
                'image/jpeg',
                'image/webp',
                'image/apng'
                ],
                true
            )
        ) {
            return;
        }

        if ((int)($this->extConf['debug'] ?? 1) === 0) {
            try {
                if (!$this->getUseCdn()) {
                    $this->assureFileExists($file);
                }
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
                    $this->addMessageToFlashMessageQueue(
                        'success',
                        [0 => $percentageSaved . '%'],
                        ContextualFeedbackSeverity::INFO
                    );
                }
                $this->updateFileInformation($file);
            } catch (\Exception $e) {
                $this->saveError($file, $e);
                $this->addMessageToFlashMessageQueue(
                    'compressionFailed',
                    [0 => $e->getMessage()],
                    ContextualFeedbackSeverity::WARNING
                );
            }
        } else {
            $this->addMessageToFlashMessageQueue('debugMode', [], ContextualFeedbackSeverity::INFO);
        }
    }

    /**
     * Check if the aus driver extension exists and is loaded.
     * Additionally, it checks if CDN is actually set and
     * your located in the CDN section of the file list
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
     * @throws \Exception
     */
    public function pushToTinyPngAndStoreToCdn(File $file): int
    {
        // get the image
        // no PATH_site as file will be provided by absolute URL of the bucket or the CDN
        $publicUrl = $file->getPublicUrl();

        // get the temp file and prefix with current time
        $tempFile = $this->getPublicPath() . 'typo3temp' . DIRECTORY_SEPARATOR . time() . '_' . $this->getCdnFileName(
            $publicUrl
        );

        $source = \Tinify\fromFile($publicUrl);

        // move to temp folder
        $source->toFile($tempFile);

        // upload to CDN
        $splFileObject = new \SplFileObject($tempFile);
        $fileSize = $splFileObject->getSize();
        $this->client->putObject(
            [
                'Bucket' => $this->extConf['bucket'],
                'Key' => $file->getIdentifier(),
                'SourceFile' => $tempFile,
            ]
        );
        // remove temp file
        GeneralUtility::unlink_tempfile($tempFile);
        return (int)$fileSize;
    }

    /**
     * This only works if file does not exist
     */
    public function checkIfFolderIsCdn(File $file): bool
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

    public function getCdnFileName(string $fileName): string
    {
        return preg_replace('/^.*\/(.*)$/', '$1', $fileName);
    }

    protected function getPublicPath(): string
    {
        return Environment::getPublicPath() . '/';
    }

    protected function getAbsoluteFileName(File $file): string
    {
        return urldecode(
            rtrim(Environment::getPublicPath(), '/') . '/' . ltrim($file->getPublicUrl(), '/')
        );
    }

    /**
     * @throws \Exception
     */
    protected function assureFileExists(File $file): void
    {
        $absFileName = $this->getAbsoluteFileName($file);
        if (file_exists($absFileName) === false) {
            throw new \RuntimeException('Tinyimg: File does not exist: ' . $absFileName, 1575270381);
        }
        if ((int)filesize($absFileName) === 0) {
            throw new \RuntimeException('Tinyimg: Filesize is 0: ' . $absFileName, 1575270380);
        }
    }

    /**
     * @throws Exception
     */
    protected function isFileInExcludeFolder(File $file): bool
    {
        if (!empty($this->extConf['excludeFolders'])) {
            $excludeFolders = GeneralUtility::trimExplode(',', $this->extConf['excludeFolders'], true);
            $identifier = $file->getIdentifier();
            foreach ($excludeFolders as $excludeFolder) {
                if (str_starts_with($identifier, $excludeFolder)) {
                    $this->addMessageToFlashMessageQueue(
                        'folderExcluded',
                        [0 => $excludeFolder],
                        ContextualFeedbackSeverity::INFO
                    );
                    return true;
                }
            }
        }
        return false;
    }

    protected function getApiKey(): string
    {
        return (string)$this->extConf['apiKey'];
    }

    protected function getUseCdn(): bool
    {
        return (bool)$this->extConf['useCdn'];
    }

    protected function updateFileInformation(File $file): void
    {
        $storage = $file->getStorage();
        $fileIndexer = GeneralUtility::makeInstance(Indexer::class, $storage);
        $fileIndexer->updateIndexEntry($file);
    }

    /**
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
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
            clearstatcache();
            $splFileObject = new \SplFileObject($this->getAbsoluteFileName($file));
            return (int)$splFileObject->getSize();
        } catch (\Exception) {
            return null;
        }
    }

    protected function isCli(): bool
    {
        return Environment::isCli();
    }

    /**
     * @throws Exception
     */
    protected function addMessageToFlashMessageQueue(
        string $key,
        array $replaceMarkers = [],
        ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::ERROR
    ): void {
        if ($this->isCli()) {
            return;
        }

        $message = LocalizationUtility::translate(
            'LLL:EXT:tinyimg/Resources/Private/Language/locallang.xlf:flashMessage.message.' . $key,
            null,
            $replaceMarkers
        );
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            LocalizationUtility::translate(
                'LLL:EXT:tinyimg/Resources/Private/Language/locallang.xlf:flashMessage.title'
            ),
            $severity,
            true
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * @throws UnknownObjectException
     * @throws IllegalObjectTypeException
     */
    protected function saveError(File $file, \Exception $e): void
    {
        /** @var \Schmitzal\Tinyimg\Domain\Model\File $extbaseFileObject */
        $extbaseFileObject = $this->fileRepository->findByUid($file->getUid());
        $extbaseFileObject->setCompressed(false);
        $extbaseFileObject->setCompressError($e->getCode() . ' : ' . $e->getMessage());
        $this->fileRepository->update($extbaseFileObject);
        $this->persistenceManager->persistAll();
    }
}
