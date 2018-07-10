<?php
/**
 * Created by PhpStorm.
 * User: sarcoma
 * Date: 10/07/18
 * Time: 10:08
 */

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class VideoPath extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('videoPath', [$this, 'videoPathFilter']),
        ];
    }

    public function videoPathFilter($filename, $size = '1280x720')
    {
        $filenameParts = pathinfo($filename);
        $filePath = 'https://s3-eu-west-1.amazonaws.com/oaccvideouploader/videos/uploads/';
        $filePath .= $filenameParts['filename'].'_'.$size.'.mp4';

        return $filePath;
    }
}
