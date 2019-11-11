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
     * @var string
     */
    protected $compressError = '';

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

    /**
     * @return string
     */
    public function getCompressError(): string
    {
        return $this->compressError;
    }

    /**
     * @param string $compressError
     */
    public function setCompressError(string $compressError): void
    {
        $this->compressError = $compressError;
    }

    /**
     * @return void
     */
    public function resetCompressError(): void
    {
        $this->setCompressError('');
    }
}
