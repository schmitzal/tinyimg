<?php
namespace Schmitzal\Tinyimg\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class FileStorage
 * @package Schmitzal\Tinyimg\Domain\Model
 */
class FileStorage extends AbstractEntity
{
    /**
     * @var string
     */
    protected $name = '';
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var bool
     */
    protected $default = false;
    /**
     * @var bool
     */
    protected $browsable = false;
    /**
     * @var bool
     */
    protected $public = false;
    /**
     * @var bool
     */
    protected $writable = false;
    /**
     * @var bool
     */
    protected $online = false;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault(bool $default)
    {
        $this->default = $default;
    }

    /**
     * @return bool
     */
    public function isBrowsable(): bool
    {
        return $this->browsable;
    }

    /**
     * @param bool $browsable
     */
    public function setBrowsable(bool $browsable)
    {
        $this->browsable = $browsable;
    }

    /**
     * @return bool
     */
    public function isPublic(): bool
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic(bool $public)
    {
        $this->public = $public;
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * @param bool $writable
     */
    public function setWritable(bool $writable)
    {
        $this->writable = $writable;
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @param bool $online
     */
    public function setOnline(bool $online)
    {
        $this->online = $online;
    }
}
