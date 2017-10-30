<?php
namespace Schmitzal\Tinyimg\Command;

use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\ProcessedFileRepository;
use TYPO3\CMS\Core\Resource\ResourceStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

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
     */
    public function compressCommand()
    {
        /** @var FileStorage $fileStorage */
        foreach ($this->fileStorageRepository->findAll() as $fileStorage) {
            $this->compressImagesInStorage($fileStorage);
        }
    }

    /**
     * @param FileStorage $fileStorage
     */
    protected function compressImagesInStorage(FileStorage $fileStorage)
    {
        $fileStorage = $this->resourceFactory->getStorageObject($fileStorage->getUid());
        /** @var FileExtensionFilter $fileExtensionFilter */
        $fileExtensionFilter = GeneralUtility::makeInstance(FileExtensionFilter::class);
        $fileExtensionFilter->setAllowedFileExtensions(['png', 'jpg', 'jpeg']);
        $fileStorage->setFileAndFolderNameFilters([[$fileExtensionFilter, 'filterFileList']]);
        $rootFolder = $fileStorage->getFolder('/');
        $rootFiles = $fileStorage->getFilesInFolder($rootFolder);
        /*
         * @TODO: Check if this way would be faster, instead of running again and again through a foreach
         * $rootFiles = $fileStorage->getFilesInFolder($rootFolder, 0, 0, true, true);
         */
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump(current($rootFiles)->getProperties());die;

        $this->compressImages($rootFiles, $rootFolder);
        $this->compressImagesInFolder($fileStorage, $rootFolder);

        $this->clearProcessedFiles();
    }

    /**
     * @param ResourceStorage $fileStorage
     * @param Folder $folder
     * @return void
     */
    protected function compressImagesInFolder(ResourceStorage $fileStorage, Folder $folder)
    {
        $subFolders = $fileStorage->getFoldersInFolder($folder);

        foreach ($subFolders as $subFolder) {
            $this->compressImagesInFolder($fileStorage, $subFolder);

            $this->compressImages($fileStorage->getFilesInFolder($subFolder), $subFolder);
        }
    }

    /**
     * @param array $files
     * @param Folder $folder
     * @return void
     */
    protected function compressImages(array $files, Folder $folder)
    {
        /** @var File $file */
        foreach ($files as $file) {
            if ($file instanceof File) {
//                $folder = $file->getParentFolder();
                $this->compressImageService->initializeCompression($file, $folder);
            }
        }
    }

    /**
     * Remove all processed files, so they get generated again after being compressed
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