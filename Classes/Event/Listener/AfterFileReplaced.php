<?php

declare(strict_types=1);

namespace Schmitzal\Tinyimg\Event\Listener;

use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Resource\Event\AfterFileReplacedEvent;
use Schmitzal\Tinyimg\Service\CompressImageService;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

#[AsEventListener(identifier: 'tinyimg-after-file-replaced-event')]
final readonly class AfterFileReplaced
{
    public function __construct(private CompressImageService $compressImageService)
    {
    }

    /**
     * @throws Exception
     * @throws UnknownObjectException
     * @throws IllegalObjectTypeException
     */
    public function __invoke(AfterFileReplacedEvent $event): AfterFileReplacedEvent
    {
        /** @var File $file */
        $file = $event->getFile();
        $this->compressImageService->initializeCompression($file);
        return $event;
    }
}
