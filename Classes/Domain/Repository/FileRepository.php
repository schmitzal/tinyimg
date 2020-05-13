<?php

namespace Schmitzal\Tinyimg\Domain\Repository;

use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class FileRepository
 * @package Schmitzal\Tinyimg\Domain\Repository
 */
class FileRepository extends Repository
{
    /**
     * Do not respect storage pid for domain records
     */
    public function createQuery(): QueryInterface
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query;
    }

    /**
     * @param FileStorage $storage
     * @param int $limit
     * @param array $excludeFolders
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findAllNonCompressedInStorageWithLimit(FileStorage $storage, $limit = 100, $excludeFolders = []): QueryResultInterface
    {
        $query = $this->createQuery();

        $excludeFoldersConstraints = [];
        foreach ($excludeFolders as $excludeFolder) {
            $excludeFoldersConstraints[] = $query->logicalNot($query->like('identifier', $excludeFolder . '%'));
        }

        $query->matching(
            $query->logicalAnd(
                array_merge(
                    [
                        $query->equals('storage', $storage),
                        $query->equals('compressed', false),
                        $query->equals('missing', false),
                        $query->equals('compress_error', ''),
                        $query->in('mime_type', ['image/png', 'image/jpeg'])
                    ],
                    $excludeFoldersConstraints
                )
            )
        );
        $query->setLimit($limit);

        return $query->execute();
    }
}
