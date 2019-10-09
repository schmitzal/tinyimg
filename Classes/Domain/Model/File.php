<?php

namespace Schmitzal\Tinyimg\Domain\Model;

/**
 * Class File
 * @package Schmitzal\Tinyimg\Domain\Model
 */
class File extends \TYPO3\CMS\Extbase\Domain\Model\File
{
    /**
     * @var int
     */
    protected $storage = 0;
    /**
     * @var bool
     */
    protected $compressed = false;

    /**
     * @return int
     */
    public function getStorage(): int
    {
        return $this->storage;
    }

    /**
     * @param int $storage
     */
    public function setStorage(int $storage): void
    {
        $this->storage = $storage;
    }

    /**
     * @return bool
     */
    public function isCompressed(): bool
    {
        return $this->compressed;
    }

    /**
     * @param bool $compressed
     */
    public function setCompressed(bool $compressed): void
    {
        $this->compressed = $compressed;
    }
}
