<?php

namespace Schmitzal\Tinyimg\Domain\Model;

class File extends \TYPO3\CMS\Extbase\Domain\Model\File
{
    protected int $storage = 0;
    protected bool $compressed = false;
    protected string $compressError = '';

    public function getStorage(): int
    {
        return $this->storage;
    }

    public function setStorage(int $storage): void
    {
        $this->storage = $storage;
    }

    public function isCompressed(): bool
    {
        return $this->compressed;
    }

    public function setCompressed(bool $compressed): void
    {
        $this->compressed = $compressed;
    }

    public function getCompressError(): string
    {
        return $this->compressError;
    }

    public function setCompressError(string $compressError): void
    {
        $this->compressError = $compressError;
    }

    public function resetCompressError(): void
    {
        $this->setCompressError('');
    }
}
