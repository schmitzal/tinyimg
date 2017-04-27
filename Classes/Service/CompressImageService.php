<?php
namespace Schmitzal\Tinyimg\Service;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility;

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
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
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
        /** @var ConfigurationUtility $configurationUtility */
        $configurationUtility = $this->objectManager->get(ConfigurationUtility::class);
        $extensionConfiguration = $configurationUtility->getCurrentConfiguration('tinyimg');
        return $extensionConfiguration['apiKey']['value'];
    }
}