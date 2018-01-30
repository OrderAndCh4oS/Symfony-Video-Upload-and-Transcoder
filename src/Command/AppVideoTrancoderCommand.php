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
        $ffmpegCommand = $this->ffmpegScript($upload_destination, $file, 1);
        $process = new Process($ffmpegCommand);
        $process->start();
    }

    /**
     * @param $upload_destination
     * @param $file
     * @return string
     */
    private function ffmpegScript($upload_destination, $file, $pass): string
    {
        $file_name = pathinfo($file, PATHINFO_FILENAME);
        $settings = "-vcodec libx264 -preset slow -crf 18 -b:v 3000k -maxrate 4000k -bufsize 512k -c:a aac -b:a 128k -strict -2";
        $command = "/usr/bin/ffmpeg -y -i {$upload_destination}/{$file} ";
        $command .= "-f mp4 {$settings} -hide_banner ";
        $command .= "{$upload_destination}/{$file_name}_new.mp4 -hide_banner";
        $f = fopen('/var/www/video-uploader/var/ffmpeg.txt', 'w');
        fwrite($f, $command);
        fclose($f);

        return $command;
    }
}
