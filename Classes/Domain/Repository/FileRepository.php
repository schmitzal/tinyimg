<?php

namespace Schmitzal\Tinyimg\Domain\Repository;

use Schmitzal\Tinyimg\Domain\Model\FileStorage;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class FileRepository.
 */
class FileRepository extends Repository
{
    /**
     * Do not respect storage pid for domain records.
     */
    public function createQuery()
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query;
    }

    /**
     * @param FileStorage $storage
     * @param int         $limit
     *
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     *
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findAllNonCompressedInStorageWithLimit(FileStorage $storage, $limit = 100)
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd([
                $query->equals('storage', $storage),
                $query->equals('compressed', false),
                $query->equals('missing', false),
                $query->in('extension', ['png', 'jpg', 'jpeg']),
            ])
        );
        $query->setLimit($limit);

        return $query->execute();
    }
}
