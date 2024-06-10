<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

$sysFileColumns = [
    'compressed' => [
        'exclude' => true,
        'label' => 'Compressed',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ],
    'compress_error' => [
        'exclude' => true,
        'label' => 'Compression Error',
        'config' => [
            'type' => 'text',
            'default' => ''
        ]
    ]
];

ExtensionManagementUtility::addTCAcolumns('sys_file', $sysFileColumns);
ExtensionManagementUtility::addToAllTCAtypes('sys_file', 'compress_error');
