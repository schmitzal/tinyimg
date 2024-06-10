<?php

namespace Schmitzal\Tinyimg\Command;

use Schmitzal\Tinyimg\Domain\Model\File;
use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use Schmitzal\Tinyimg\Domain\Repository\FileRepository;
use Schmitzal\Tinyimg\Domain\Repository\FileStorageRepository;
use Schmitzal\Tinyimg\Service\CompressImageService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\Processing\FileDeletionAspect;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

#[AsCommand(
    name: 'tinyimg:compressImages',
    description: 'Compress uncompressed images',
)]
final class CompressImagesCommand extends Command
{
    private const int DEFAULT_LIMIT_TO_PROCESS = 100;

    public function __construct(
        private readonly FileStorageRepository $fileStorageRepository,
        private readonly FileRepository $fileRepository,
        private readonly ResourceFactory $resourceFactory,
        private readonly CompressImageService $compressImageService,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument(
            'limit',
            InputArgument::OPTIONAL,
            'limit of files to compress',
            self::DEFAULT_LIMIT_TO_PROCESS
        );
    }

    /**
     * @throws InvalidQueryException
     * @throws IllegalObjectTypeException
     * @throws FileDoesNotExistException
     * @throws UnknownObjectException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws NoSuchCacheGroupException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = (int)$input->getArgument('limit');
        $settings = (GeneralUtility::makeInstance(ExtensionConfiguration::class))->get('tinyimg');
        /** @var FileStorage $fileStorage */
        foreach ($this->fileStorageRepository->findAll() as $fileStorage) {
            $excludeFolders = GeneralUtility::trimExplode(',', (string)($settings['excludeFolders'] ?? ''), true);
            $files = $this->fileRepository->findAllNonCompressedInStorageWithLimit(
                $fileStorage,
                $limit,
                $excludeFolders
            );
            if ($files->count() > 0) {
                $this->compressImages($files);
                $this->clearPageCache();
            }
        }

        return 0;
    }

    /**
     * @throws FileDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws Exception
     */
    protected function compressImages(QueryResultInterface $files): void
    {
        $fileDeletionAspect = GeneralUtility::makeInstance(FileDeletionAspect::class);
        /** @var File $file */
        foreach ($files as $file) {
            if ($file instanceof File) {
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
     * @throws NoSuchCacheGroupException
     */
    protected function clearPageCache(): void
    {
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->flushCachesInGroup('pages');
    }
}
