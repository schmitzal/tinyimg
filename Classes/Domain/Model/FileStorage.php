<?php

namespace Schmitzal\Tinyimg\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class FileStorage.
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * @param bool $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * @return bool
     */
    public function isBrowsable()
    {
        return $this->browsable;
    }

    /**
     * @param bool $browsable
     */
    public function setBrowsable($browsable)
    {
        $this->browsable = $browsable;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * @param bool $public
     */
    public function setPublic($public)
    {
        $this->public = $public;
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @param bool $writable
     */
    public function setWritable($writable)
    {
        $this->writable = $writable;
    }

    /**
     * @return bool
     */
    public function isOnline()
    {
        return $this->online;
    }

    /**
     * @param bool $online
     */
    public function setOnline($online)
    {
        $this->online = $online;
    }
}
