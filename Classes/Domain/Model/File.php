<?php
namespace Schmitzal\Tinyimg\Domain\Model;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class FileStorage
 * @package Schmitzal\Tinyimg\Domain\Model
 */
class FileStorage extends File
{
    /**
     * @var string
     */
    protected $name = '';

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
}