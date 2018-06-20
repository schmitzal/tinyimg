<?php

$EM_CONF[$_EXTKEY] = [
    'title'            => 'Tinyimg',
    'description'      => 'Image compression for all pngs and jpgs uploaded to the backend (using the tinypng API)',
    'category'         => 'be',
    'author'           => 'Alessandro Schmitz',
    'author_email'     => 'alessandro.schmitz@interlutions.de',
    'author_company'   => 'Interlutions GmbH',
    'version'          => '1.2.0',
    'shy'              => '',
    'dependencies'     => '',
    'conflicts'        => '',
    'priority'         => '',
    'module'           => '',
    'state'            => 'beta',
    'internal'         => '',
    'uploadfolder'     => 0,
    'createDirs'       => '',
    'modify_tables'    => '',
    'clearCacheOnLoad' => 0,
    'lockType'         => '',
    'constraints'      => [
        'depends' => [
            'typo3' => '7.6.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
    'suggests' => [],
];
