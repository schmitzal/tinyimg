<?php

declare(strict_types=1);

namespace Schmitzal\Tinyimg\EventListener;

use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

final class AfterBackendPageRenderEventListener
{
    public function __construct(private readonly PageRenderer $pageRenderer)
    {
    }

    public function __invoke(AfterBackendPageRenderEvent $event): void
    {
        $this->pageRenderer->addInlineLanguageLabel(
            'compressingLabel',
            LocalizationUtility::translate('compressingLabel', 'Tinyimg')
        );
    }
}
