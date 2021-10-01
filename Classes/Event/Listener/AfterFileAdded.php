<?php

namespace Schmitzal\Tinyimg\Event\Listener;

use Schmitzal\Tinyimg\Service\CompressImageService;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedEvent;

class AfterFileAdded
{
    /**
     * @var CompressImageService
     */
    protected $compressImageService;

    public function __construct(CompressImageService $compressImageService)
    {
        $this->compressImageService = $compressImageService;
    }

    public function __invoke(AfterFileAddedEvent $event): AfterFileAddedEvent
    {
        $this->compressImageService->initializeCompression($event->getFile());
        return $event;
    }

}
