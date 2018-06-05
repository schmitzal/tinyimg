<?php
namespace Schmitzal\Tinyimg\Command;

use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class CompressImagesCommandController
 * @package Schmitzal\Tinyimg\Command
 */
class CompressImagesCommandController extends CommandController
{
    /**
     * @var \Schmitzal\Tinyimg\Domain\Repository\FileStorageRepository
     * @inject
     */
    protected $fileStorageRepository;
    /**
     * @var \Schmitzal\Tinyimg\Domain\Repository\FileRepository
     * @inject
     */
    protected $fileRepository;
    /**
     * @var \TYPO3\CMS\Core\Resource\ResourceFactory
     * @inject
     */
    protected $resourceFactory;
    /**
     * @var \Schmitzal\Tinyimg\Service\CompressImageService
     * @inject
     */
    protected $compressImageService;

    /**
     * Command: compress
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     * @throws \TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function compressCommand()
    {
        /** @var FileStorage $fileStorage */
        foreach ($this->fileStorageRepository->findAll() as $fileStorage) {
            $files = $this->fileRepository->findAllNonCompressedInStorageWithLimit($fileStorage, 100);

            $this->compressImages($files);

            $this->clearProcessedFiles();
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
    protected function compressImages(QueryResultInterface $files)
    {
        /** @var \Schmitzal\Tinyimg\Domain\Model\File $file */
        foreach ($files as $file) {
            if ($file instanceof \Schmitzal\Tinyimg\Domain\Model\File) {
                $file = $this->resourceFactory->getFileObject($file->getUid());
                $folder = $this->resourceFactory->getFolderObjectFromCombinedIdentifier($file->getParentFolder()->getIdentifier());
                if (filesize(GeneralUtility::getFileAbsFileName($file->getPublicUrl())) > 0) {
                    $this->compressImageService->initializeCompression($file, $folder);
                }
            }
        }
    }

    /**
     * Remove all processed files, so they get generated again after being compressed
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheGroupException
     */
    protected function clearProcessedFiles()
    {
        /** @var ProcessedFileRepository $repository */
        $repository = GeneralUtility::makeInstance(ProcessedFileRepository::class);
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);

        $repository->removeAll();
        $cacheManager->flushCachesInGroup('pages');
    }
}
