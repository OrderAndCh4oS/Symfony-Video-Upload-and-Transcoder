<?php

namespace App\Command;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Process\Process;

class AppVideoTranscoderCommand extends Command
{
    protected static $defaultName = 'app:video-transcoder';
    /**
     * @var string
     */
    private $command = "";
    private $file;
    private $upload_destination;
    private $file_name;
    /**
     * @var ContainerInterface
     */
    private $container;
    private $files = [];

    public function __construct(?string $name = null, ContainerInterface $container)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Transcode video to MP4')
            ->addArgument('arg1', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('arg2', InputArgument::REQUIRED, 'File name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->upload_destination = $input->getArgument('arg1');
        $this->file = $input->getArgument('arg2');
        $this->file_name = pathinfo($this->file, PATHINFO_FILENAME);
        $this->ffmpegScript();
        $process = new Process($this->command);
        $process->start();
        $process->wait();
        if ($process->isSuccessful()) {
            try {
                /** @var \League\Flysystem\Filesystem $filesystem */
                $filesystem = $this->container->get('oneup_flysystem.mount_manager')->getFilesystem('prefix');
                foreach ($this->files as $filePath) {
                    $file = new File($filePath);
                    if ($file->isFile()) {
                        $stream = fopen($file->getRealPath(), 'r+');
                        $filesystem->writeStream('uploads/'.$file->getFilename(), $stream);
                        fclose($stream);
                    }
                }
                $f = fopen('/var/www/video-uploader/var/dump.txt', 'w');
                fwrite($f, 'got here');
                fclose($f);
            } catch (NotFoundExceptionInterface $e) {
                $f = fopen('/var/www/video-uploader/var/error.txt', 'w');
                fwrite($f, 'fly fail not found');
                fclose($f);
            } catch (ContainerExceptionInterface $e) {
                $f = fopen('/var/www/video-uploader/var/error.txt', 'w');
                fwrite($f, 'fly fail container exception');
                fclose($f);
            }
        }
    }

    private function ffmpegScript()
    {
        $this->command = "/usr/bin/ffmpeg -y -i {$this->upload_destination}/{$this->file} ";
        $this->command .= $this->addSize(360);
        $this->command .= $this->addSize(720);
        $this->command .= $this->addSize(1080);
        $this->command .= "-hide_banner";
    }

    /**
     * @param $height
     */
    private function addSize($height)
    {
        $settings = "-f mp4 -vcodec libx264 -preset slow -crf 18 -b:v 3000k -maxrate 4000k -bufsize 512k -c:a aac -b:a 128k -strict -2";
        $width = $height * (16 / 9);
        $dimensions = "{$width}x{$height}";
        $this->command .= "-s {$dimensions} {$settings} ";
        $this->command .= "{$this->upload_destination}/{$this->file_name}_{$dimensions}.mp4 ";
        $this->files[] = "{$this->upload_destination}/{$this->file_name}_{$dimensions}.mp4";
    }
}
