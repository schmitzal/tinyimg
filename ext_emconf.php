<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Tinyimg',
    'description' => 'Image compression for all pngs and jpgs uploaded to the backend (using the tinypng API)',
    'category' => 'be',
    'author' => 'Alessandro Schmitz',
    'author_email' => 'alessandro.schmitz@interlutions.de',
    'author_company' => 'Interlutions GmbH',
    'version' => '1.7.0',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
