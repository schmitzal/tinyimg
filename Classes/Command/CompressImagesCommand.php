<?php

namespace Schmitzal\Tinyimg\Command;

use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use Schmitzal\Tinyimg\Domain\Repository\FileRepository;
use Schmitzal\Tinyimg\Domain\Repository\FileStorageRepository;
use Schmitzal\Tinyimg\Service\CompressImageService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use TYPO3\CMS\Core\Resource\Processing\FileDeletionAspect;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

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

    public function __construct(
        FileStorageRepository $fileStorageRepository,
        FileRepository $fileRepository,
        ResourceFactory $resourceFactory,
        CompressImageService $compressImageService,
        string $name = null
    ) {
        $this->fileStorageRepository = $fileStorageRepository;
        $this->fileRepository = $fileRepository;
        $this->resourceFactory = $resourceFactory;
        $this->compressImageService = $compressImageService;
        parent::__construct($name);
    }

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
     * Executes the command for adding the lock file
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int)$input->getArgument('limit');
        $settings = (GeneralUtility::makeInstance(ExtensionConfiguration::class))->get('tinyimg');
        /** @var FileStorage $fileStorage */
        foreach ($this->fileStorageRepository->findAll() as $fileStorage) {
            $excludeFolders = GeneralUtility::trimExplode(',', (string)($settings['excludeFolders'] ?? ''), true);
            $files = $this->fileRepository->findAllNonCompressedInStorageWithLimit($fileStorage, $limit, $excludeFolders);
            if (!empty($files)) {
                $this->compressImages($files);
                $this->clearPageCache();
            }
        }

        return 0;
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
                $fileDeletionAspect->cleanupProcessedFilesPostFileReplace(
                    new AfterFileReplacedEvent($file, '')
                );
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
}
