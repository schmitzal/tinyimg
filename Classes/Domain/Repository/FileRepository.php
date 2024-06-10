<?php

namespace Schmitzal\Tinyimg\Domain\Repository;

use Schmitzal\Tinyimg\Domain\Model\File;
use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class FileRepository extends Repository
{
    public function createQuery(): QueryInterface
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query;
    }

    /**
     * @throws InvalidQueryException
     */
    public function findAllNonCompressedInStorageWithLimit(
        FileStorage $storage,
        int $limit = 100,
        array $excludeFolders = []
    ): QueryResultInterface {
        $query = $this->createQuery();

        $excludeFoldersConstraints = [];
        foreach ($excludeFolders as $excludeFolder) {
            $excludeFoldersConstraints[] = $query->logicalNot(
                $query->like('identifier', $excludeFolder . '%')
            );
        }

        $query->matching(
            $query->logicalAnd(
                ...array_merge(
                    [
                           $query->equals('storage', $storage),
                           $query->equals('compressed', false),
                           $query->equals('missing', false),
                           $query->logicalOr(
                               $query->equals('compress_error', null),
                               $query->equals('compress_error', ''),
                           ),
                           $query->in(
                               'mime_type',
                               [
                                   'image/png',
                                   'image/jpeg',
                               ]
                           ),
                       ],
                    $excludeFoldersConstraints
                )
            )
        );
        $query->setLimit($limit);

        return $query->execute();
    }
}
