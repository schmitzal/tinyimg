<?php

$sysFileColumns = [
    'compressed' => [
        'exclude' => true,
        'label' => 'sys_file.compressed',
        'config' => [
            'type' => 'check',
            'default' => 0
        ]
    ]
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file', $sysFileColumns);