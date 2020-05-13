<?php

namespace Schmitzal\Tinyimg\Command;

use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use Schmitzal\Tinyimg\Domain\Repository\FileRepository;
use Schmitzal\Tinyimg\Domain\Repository\FileStorageRepository;
use Schmitzal\Tinyimg\Service\CompressImageService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Resource\Processing\FileDeletionAspect;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class CompressImagesCommandController
 * @package Schmitzal\Tinyimg\Command
 */
class CompressImagesCommand extends Command
{

    const DEFAULT_LIMIT_TO_PROCESS = 100;

    /**
     * @var \Schmitzal\Tinyimg\Domain\Repository\FileStorageRepository
     */
    protected $fileStorageRepository;

    /**
     * @var \Schmitzal\Tinyimg\Domain\Repository\FileRepository
     */
    protected $fileRepository;

    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     */
    protected $resourceFactory;

    /**
     * @var \Schmitzal\Tinyimg\Service\CompressImageService
     */
    protected $compressImageService;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('compressImages:compress')
            ->setDescription('compress uncompressed images')
            ->addArgument(
                'limit',
                InputArgument::OPTIONAL,
                'limit of files to compress',
                self::DEFAULT_LIMIT_TO_PROCESS
            );
    }

    /**
     * @throws \TYPO3\CMS\Extbase\Object\Exception
     */
    protected function initializeDependencies(): void
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->fileStorageRepository = $this->objectManager->get(FileStorageRepository::class);
        $this->fileRepository = $this->objectManager->get(FileRepository::class);
        $this->resourceFactory = $this->objectManager->get(ResourceFactory::class);
        $this->compressImageService = $this->objectManager->get(CompressImageService::class);
    }

    /**
     * Executes the command for adding the lock file
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $limit = (int)$input->getArgument('limit');
        $this->initializeDependencies();
        $settings = $this->getTypoScriptConfiguration();
        /** @var FileStorage $fileStorage */
        foreach ($this->fileStorageRepository->findAll() as $fileStorage) {
            $excludeFolders = GeneralUtility::trimExplode(',', (string)$settings['excludeFolders'], true);
            $files = $this->fileRepository->findAllNonCompressedInStorageWithLimit($fileStorage, $limit, $excludeFolders);
            $this->compressImages($files);
            $this->clearPageCache();
        }
    }

    /**
     * @param QueryResultInterface $files
     * @return void
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    protected function compressImages(QueryResultInterface $files): void
    {
        /** @var FileDeletionAspect $fileDeletionAspect */
        $fileDeletionAspect = GeneralUtility::makeInstance(FileDeletionAspect::class);
        /** @var \Schmitzal\Tinyimg\Domain\Model\File $file */
        foreach ($files as $file) {
            if ($file instanceof \Schmitzal\Tinyimg\Domain\Model\File) {
                $file = $this->resourceFactory->getFileObject($file->getUid());
                $this->compressImageService->initializeCompression($file);
                $fileDeletionAspect->cleanupProcessedFilesPostFileReplace($file, '');
            }
        }
    }

    /**
     * Remove all processed files, so they get generated again after being compressed
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     */
    protected function clearPageCache(): void
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroup('pages');
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
}
