<?php

use Schmitzal\Tinyimg\Domain\Model\File;
use Schmitzal\Tinyimg\Domain\Model\FileStorage;

return [
    FileStorage::class => [
        'tableName' => 'sys_file_storage'
    ],
    File::class => [
        'tableName' => 'sys_file'
    ]
];
