<?php

$sysFileColumns = [
    'compressed' => [
        'exclude' => true,
        'label' => 'Compression Status',
        'config' => [
            'type' => 'select',
            'default' => 0,
            'items' => [
                ['Not compressed', 0],
                ['Compressed', 1],
                ['Excluded from compressing process due to error', 2]
            ],
        ],
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

$GLOBALS['TCA']['sys_file']['interface']['showRecordFieldList'] = $GLOBALS['TCA']['sys_file']['interface']['showRecordFieldList'] . ',compressed,compress_log';
