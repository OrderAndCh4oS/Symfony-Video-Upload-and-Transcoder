<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class AppVideoTrancoderCommand extends Command
{
    protected static $defaultName = 'app:video-trancoder';

    protected function configure()
    {
        $this
            ->setDescription('Transcode video to MP4')
            ->addArgument('arg1', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('arg2', InputArgument::REQUIRED, 'File name');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $upload_destination = $input->getArgument('arg1');
        $file = $input->getArgument('arg2');
        $file_name = pathinfo($file, PATHINFO_FILENAME);
        $file_ext = pathinfo($file, PATHINFO_EXTENSION);
        $ffmpegCommand = "/usr/bin/ffmpeg -i {$upload_destination}/{$file} -f mp4 -vcodec libx264 -preset slow -profile:v main -acodec aac -strict -2 {$upload_destination}/{$file_name}_newer.{$file_ext} -hide_banner";
        $process = new Process($ffmpegCommand);
        $process->start();
    }
}
