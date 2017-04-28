<?php
namespace Schmitzal\Tinyimg\Service;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

require_once(__DIR__ . '/../../vendor/autoload.php');

/**
 * Class CompressImageService
 * @package Schmitzal\Tinyimg\Service
 */
class CompressImageService
{
    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @inject
     */
    protected $objectManager;

    /**
     * @param File $file
     * @param Folder $folder
     */
    public function initializeCompression($file, $folder)
    {
        \Tinify\setKey($this->getApiKey());

        if (in_array($file->getExtension(), ['png', 'jpg'], true)) {
            $publicUrl = PATH_site . $file->getPublicUrl();
            $source = \Tinify\fromFile($publicUrl);
            $source->toFile($publicUrl);
        }
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        /** @var ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->objectManager->get(ConfigurationUtility::class);
        $extensionConfiguration = $configurationUtility->getCurrentConfiguration('tinyimg');
        return $extensionConfiguration['apiKey']['value'];
    }
}
