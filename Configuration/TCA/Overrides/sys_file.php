<?php

$sysFileColumns = [
    'compressed' => [
        'exclude' => true,
        'label' => 'sys_file.compressed',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ],
    'compress_log' => [
        'exclude' => true,
        'label' => 'Compression Error',
        'config' => [
            'type' => 'text',
            'default' => ''
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file', $sysFileColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file', 'compress_log', '', '');
