<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class PictureSize
{
    /**
     * @var string | null
     * @Assert\Choice(choices={"sm", "md", "lg"}, message="Undefined size type")
     */
    private $imageSize;

    /**
     * @return string|null
     */
    public function getImageSize(): ?string
    {
        return $this->imageSize;
    }

    /**
     * @param string|null $imageSize
     */
    public function setImageSize(?string $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

}