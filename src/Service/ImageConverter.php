<?php

namespace App\Service;

class ImageConverter
{
    /**
     * @throws \ImagickException
     */
    public function convert(\Imagick $image, ?string $type): \Imagick
    {
        if($type === 'original') {
            return $image;
        }

        $sizing = [
            'sm' => 150,
            'md' => 400,
            'lg' => 800
        ];

        $maxSize = array_key_exists($type, $sizing) ? $sizing[$type] : 800;

        $height = $image->getImageHeight();
        $width = $image->getImageWidth();
        if($height === $width) {
            $resizedWidth = $resizedHeight = $maxSize;
        } elseif($height > $width) {
            $resizedHeight = $maxSize;
            $resizedWidth = floor(($width * $resizedHeight) / $height);
        } else {
            $resizedWidth = $maxSize;
            $resizedHeight = floor(($resizedWidth * $height) / $width);
        }

        $image->resizeImage($resizedWidth, $resizedHeight, \Imagick::FILTER_GAUSSIAN, 0);

        return $image;
    }
}