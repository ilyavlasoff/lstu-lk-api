<?php

namespace App\Service;

class ImageConverter
{
    /**
     * @throws \ImagickException
     */
    public function convert(\Imagick $image, ?string $type, bool $cropToSquare): \Imagick
    {
        if($type === 'original') {
            return $image;
        }

        $sizing = [
            'sm' => 150,
            'md' => 400,
            'lg' => 800
        ];

        $resizedWidth = $resizedHeight = array_key_exists($type, $sizing) ? $sizing[$type] : 800;

        $height = $image->getImageHeight();
        $width = $image->getImageWidth();

        $dl = $height - $width;
        if($cropToSquare) {
            if($dl > 0) {
                $image->cropImage($width, $width, 0, $dl / 2);
            } elseif($dl < 0) {
                $image->cropImage($height, $height, $dl / 2, 0);
            }
        } else {
            if ($dl > 0) {
                $resizedWidth = floor(($width * $resizedHeight) / $height);
            } elseif($dl < 0) {
                $resizedHeight = floor(($resizedWidth * $height) / $width);
            }
        }

        $image->resizeImage($resizedWidth, $resizedHeight, \Imagick::FILTER_LANCZOS, 0.9, false);

        return $image;
    }
}