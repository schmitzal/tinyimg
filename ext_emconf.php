<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'tinyimg',
    'description' => 'Image compression for all pngs and jpgs uploaded to the backend (using the tinypng API)',
    'category' => 'plugin',
    'author' => 'Alessandro Schmitz',
    'author_email' => 'alessandro.schmitz@open.de',
    'author_company' => 'OPEN Digitalgruppe GmbH',
    'state' => 'stable',
    'version' => '1.9.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];