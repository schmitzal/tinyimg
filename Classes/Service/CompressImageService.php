<?php
namespace Schmitzal\Tinyimg\Service;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Extbase\Object\ObjectManager;

require_once(__DIR__ . '/../../vendor/autoload.php');

/**
 * Class CompressImageSerivce
 * @package Schmitzal\Tinyimg\Service
 */
class CompressImageService
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * CompressImageService constructor.
     * @param \TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager
     */
    public function __construct(\TYPO3\CMS\Extbase\Object\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param File $file
     * @param Folder $folder
     */
    public function initializeCompression($file, $folder)
    {
        \Tinify\setKey($this->getApiKey());

        if (in_array($file->getExtension(), ['png', 'jpg'], true)) {
            $source = \Tinify\fromFile(PATH_site . $file->getPublicUrl());
            $source->toFile(PATH_site . $file->getPublicUrl());
        }
    }

    /**
     * @return string
     */
    protected function getApiKey()
    {
        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
        $extensionConfiguration = $configurationUtility->getCurrentConfiguration('tinyimg');
        return $extensionConfiguration['apiKey']['value'];
    }
}