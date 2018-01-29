<?php

namespace App\Listener;

use App\Entity\Video;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;
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
        $application = new Application($this->kernel);
        $application->setAutoExit(false);
        $input = new ArrayInput(
            array(
                'command' => 'app:video-trancoder',
                // (optional) define the value of command arguments
                'arg1' => $mapping->getUploadDestination(),
                'arg2' => $object->getVideoName(),
            )
        );
        // You can use NullOutput() if you don't need the output
        $output = new NullOutput();
        try {
            $application->run($input, $output);
        } catch (\Exception $e) {
            $this->logger->error('Failed to run VideoUploaderListener application');
        }
    }

}
