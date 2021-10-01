<?php

namespace Schmitzal\Tinyimg\Event\Listener;

use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use Schmitzal\Tinyimg\Service\CompressImageService;

class AfterFileReplaced
{

    /**
     * @var CompressImageService
     */
    protected $compressImageService;

    public function __construct(CompressImageService $compressImageService)
    {
        $this->compressImageService = $compressImageService;
    }

    public function __invoke(AfterFileReplacedEvent $event): AfterFileReplacedEvent
    {
        $this->compressImageService->initializeCompression($event->getFile());
        return $event;
    }

}
