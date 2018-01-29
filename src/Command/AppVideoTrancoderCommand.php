<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
        $io = new SymfonyStyle($input, $output);
        $upload_destination = $input->getArgument('arg1');
        $file = $input->getArgument('arg2');
        $file_name = pathinfo($file, PATHINFO_FILENAME);
        $file_ext = pathinfo($file, PATHINFO_EXTENSION);
        $ffmpegCommand = "/usr/bin/ffmpeg -i {$upload_destination}/{$file} -f mp4 -vcodec libx264 -preset fast -profile:v main -acodec aac -strict -2 {$upload_destination}/{$file_name}_newer.{$file_ext} -hide_banner";
        $f = fopen('/var/www/video-uploader/var/test2.txt', 'w');
        fwrite($f, $ffmpegCommand);
        fclose($f);
        $process = new Process($ffmpegCommand);
        $process->run();
        if (!$process->isSuccessful()) {
            $f = fopen('/var/www/video-uploader/var/error.txt', 'w');
            fwrite($f, 'failed');
            fclose($f);
        } else {
            $f = fopen('/var/www/video-uploader/var/success.txt', 'w');
            fwrite($f, 'not failed');
            fclose($f);
        }
    }
}
