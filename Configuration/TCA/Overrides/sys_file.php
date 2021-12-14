<?php

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

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_file', $sysFileColumns);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_file', 'compress_error', '', '');


if (isset($GLOBALS['TCA']['sys_file']['interface']['showRecordFieldList'])) {
    $GLOBALS['TCA']['sys_file']['interface']['showRecordFieldList'] .= ',compressed,compress_error';
} else {
    $GLOBALS['TCA']['sys_file']['interface']['showRecordFieldList'] = 'compressed,compress_error';
}
