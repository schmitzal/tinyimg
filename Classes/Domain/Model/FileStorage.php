<?php

namespace Schmitzal\Tinyimg\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class FileStorage extends AbstractEntity
{
    protected string $name = '';
    protected string $description = '';
    protected bool $default = false;
    protected bool $browsable = false;
    protected bool $public = false;
    protected bool $writable = false;
    protected bool $online = false;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function setDefault(bool $default): void
    {
        $this->default = $default;
    }

    public function isBrowsable(): bool
    {
        return $this->browsable;
    }

    public function setBrowsable(bool $browsable): void
    {
        $this->browsable = $browsable;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(bool $public): void
    {
        $this->public = $public;
    }

    public function isWritable(): bool
    {
        return $this->writable;
    }

    public function setWritable(bool $writable): void
    {
        $this->writable = $writable;
    }

    public function isOnline(): bool
    {
        return $this->online;
    }

    public function setOnline(bool $online): void
    {
        $this->online = $online;
    }
}
