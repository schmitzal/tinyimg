<?php
namespace Schmitzal\Tinyimg\Service;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
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
     * @var array
     */
    protected $settings;

    /**
     * @param File $file
     * @param Folder $folder
     */
    public function initializeCompression($file, $folder)
    {
        \Tinify\setKey($this->getApiKey());
        $this->settings = $this->getTypoScriptConfiguration();

        if ((int)$this->settings['debug'] === 0 &&in_array($file->getExtension(), ['png', 'jpg'], true)) {
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

    /**
     * @return array
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
}
