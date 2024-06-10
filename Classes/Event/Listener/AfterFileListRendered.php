<?php

declare(strict_types=1);

namespace Schmitzal\Tinyimg\Event\Listener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Filelist\Event\ProcessFileListActionsEvent;

#[AsEventListener(identifier: 'tinyimg-after-backend-page-renderer-event')]
final readonly class AfterFileListRendered
{
    public function __construct(
        private PageRenderer $pageRenderer
    ) {
    }

    public function __invoke(ProcessFileListActionsEvent $event): void
    {
        $this->pageRenderer->loadJavaScriptModule('@schmitzal/tinyimg/ExtendedUpload.js');
        $this->pageRenderer->addCssFile('EXT:tinyimg/Resources/Public/Css/ExtendedUpload.css');
        $this->pageRenderer->addInlineLanguageLabelFile('EXT:tinyimg/Resources/Private/Language/locallang.xlf');
    }
}
