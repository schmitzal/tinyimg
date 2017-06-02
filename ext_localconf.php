<?php
defined('TYPO3_MODE') or die();

/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);

// Register slot when a file has been added
$signalSlotDispatcher->connect(
    \TYPO3\CMS\Core\Resource\ResourceStorage::class,
    'postFileAdd',
    \Schmitzal\Tinyimg\Service\CompressImageService::class,
    'initializeCompression',
    true
);

// Register slot when a file has been replaced
$signalSlotDispatcher->connect(
    \TYPO3\CMS\Core\Resource\ResourceStorage::class,
    'postFileReplace',
    \Schmitzal\Tinyimg\Service\CompressImageService::class,
    'initializeCompression',
    true
);