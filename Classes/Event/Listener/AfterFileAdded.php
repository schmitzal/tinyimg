<?php

namespace Schmitzal\Tinyimg\Event\Listener;

use Schmitzal\Tinyimg\Service\CompressImageService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedEvent;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

#[AsEventListener(identifier: 'tinyimg-after-file-added-event')]
final readonly class AfterFileAdded
{
    public function __construct(private CompressImageService $compressImageService)
    {
    }

    /**
     * @throws Exception
     * @throws UnknownObjectException
     * @throws IllegalObjectTypeException
     */
    public function __invoke(AfterFileAddedEvent $event): AfterFileAddedEvent
    {
        $this->compressImageService->initializeCompression($event->getFile());
        return $event;
    }
}
