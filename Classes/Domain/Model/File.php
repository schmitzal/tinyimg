<?php

namespace Schmitzal\Tinyimg\Domain\Model;

/**
 * Class File.
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
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param int $storage
     */
    public function setStorage($storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return bool
     */
    public function isCompressed()
    {
        return $this->compressed;
    }

    /**
     * @param bool $compressed
     */
    public function setCompressed($compressed)
    {
        $this->compressed = $compressed;
    }
}
