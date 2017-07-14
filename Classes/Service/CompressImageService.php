<?php
namespace Schmitzal\Tinyimg\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\Index\Indexer;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Class CompressImageService
 * @package Schmitzal\Tinyimg\Service
 */
class CompressImageService
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $extConf;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var S3Client
     */
    protected $client = '';

    /**
     * CompressImageService constructor.
     */
    public function initAction()
    {
        /** @var ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->objectManager->get(ConfigurationUtility::class);
        $this->extConf = $configurationUtility->getCurrentConfiguration('tinyimg');

        $this->initCdn();
    }

    /**
     * initialize the CDN
     */
    public function initCdn()
    {
        /** @var S3Client client */
        $this->client = S3Client::factory(array(
            'region' => $this->extConf['region']['value'],
            'version' => $this->extConf['version']['value'],
            'credentials' => array(
                'key' => $this->extConf['key']['value'],
                'secret' => $this->extConf['secret']['value'],
            ),
        ));
    }

    /**
     * @param File $file
     * @param Folder $folder
     */
    public function initializeCompression($file, $folder)
    {
        $this->initAction();

        \Tinify\setKey($this->getApiKey());
        $this->settings = $this->getTypoScriptConfiguration();

        if ((int)$this->settings['debug'] === 0 && in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg'], true)) {
            if ($this->checkForAmazonCdn($folder, $file)) {
                $this->pushToTinyPngAndStoreToCdn($file);
            } else {
                $publicUrl = PATH_site . $file->getPublicUrl();
                $source = \Tinify\fromFile($publicUrl);
                $source->toFile($publicUrl);
            }
        }

        $this->updateFileInformation($file);
    }

    /**
     * Check if the aus driver extension exists and is loaded.
     * Additionally it checks if CDN is actually set and
     * your located in the CDN section of the file list
     *
     * @param Folder $folder
     * @param File $file
     * @return bool
     */
    public function checkForAmazonCdn($folder, $file)
    {
        return ExtensionManagementUtility::isLoaded('aus_driver_amazon_s3') &&
        $this->getUseCdn() &&
        $this->checkIfFolderIsCdn($folder, $file);
    }

    /**
     * Creates a temp file from original resource.
     * Pushes the temp image file to tinypng compression service.
     * Overrides the original temp file with the compressed on.
     * Puts the compressed temp image to the actual storeage in file list -> CDN
     * Deletes old temp file.
     *
     * @param File $file
     */
    public function pushToTinyPngAndStoreToCdn($file)
    {
        // get the image
        // no PATH_site as file will be provided by absolute URL of the bucket or the CDN
        $publicUrl = $file->getPublicUrl();

        // get the temp file and prefix with current time
        $tempFile = PATH_site . 'typo3temp' . DIRECTORY_SEPARATOR . time() .'_'.  $this->getCdnFileName($publicUrl);

        $source = \Tinify\fromFile($publicUrl);

        // move to temp folder
        $source->toFile($tempFile);

        // upload to CDN
        try {
            $this->client->putObject([
                'Bucket' => $this->extConf['bucket']['value'],
                'Key' => $file->getIdentifier(),
                'SourceFile' => $tempFile
            ]);
        } catch(S3Exception $e) {
            throw new S3Exception($e->getMessage());
        }

        // remove temp file
        GeneralUtility::unlink_tempfile($tempFile);
    }

    /**
     * This only works if file does not exist
     *
     * @param Folder $folder
     * @param File $file
     * @return boolean
     */
    public function checkIfFolderIsCdn($folder, $file)
    {
        // if this is string, then we know, that there is already a file in the folder
        // In this case you have to check if the object in the bucket exists
        if (is_string($folder)) {
            return $this->client->doesObjectExist(
                $this->extConf['bucket']['value'],
                $file->getIdentifier()
            );
        }

        return $folder->getStorage()->getDriverType() == 'AusDriverAmazonS3';
    }

    /**
     * @param $file string
     * @return string
     */
    public function getCdnFileName($file)
    {
        return preg_replace('/^.*\/(.*)$/', '$1', $file);
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        return $this->extConf['apiKey']['value'];
    }

    /**
     * @return boolean
     */
    protected function getUseCdn()
    {
        return $this->extConf['useCdn']['value'];
    }

    /**
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getTypoScriptConfiguration()
    {
        /** @var ConfigurationManager $configurationManager */
        $configurationManager = $this->objectManager->get(ConfigurationManager::class);

        return $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'tinyimg'
        );
    }

    /**
     * @param File $file
     */
    protected function updateFileInformation($file)
    {
        /** @var Indexer $fileIndexer */
        $fileIndexer = $this->objectManager->get(Indexer::class, $file->getStorage());
        $fileIndexer->updateIndexEntry($file);
    }
}
