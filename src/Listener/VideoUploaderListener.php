<?php

namespace App\Listener;

use App\Entity\Video;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Process;
use Vich\UploaderBundle\Event\Event;
use Vich\UploaderBundle\Mapping\PropertyMapping;

class VideoUploaderListener
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * VideoUploaderListener constructor.
     * @param LoggerInterface $logger
     * @param KernelInterface $kernel
     */
    public function __construct(LoggerInterface $logger, KernelInterface $kernel)
    {
        $this->logger = $logger;
        $this->kernel = $kernel;
    }

    public function onVichUploaderPostUpload(Event $event)
    {
        if (!($event->getObject() instanceof Video) && !($event instanceof PropertyMapping)) {
            return;
        }
        /** @var Video $object */
        $object = $event->getObject();
        /** @var PropertyMapping $mapping */
        $mapping = $event->getMapping();
        $destination = $mapping->getUploadDestination();
        $name = $object->getVideoName();
        $root = $this->kernel->getRootDir();
        $environment = $this->kernel->getEnvironment();
        $process = new Process(
            "php {$root}/../bin/console app:video-transcoder {$destination} {$name} --env=".$environment
        );
        $process->start();
    }

}
