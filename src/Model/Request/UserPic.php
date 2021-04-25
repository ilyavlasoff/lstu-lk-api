<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class UserPic
{
    /**
     * @var string | null
     * @Assert\Choice(choices={"sm", "md", "lg"}, message="Undefined size type")
     * @JMS\Type("string")
     * @JMS\SerializedName("size")
     */
    private $imageSize;

    /**
     * @var string
     * @Assert\NotNull(message="Person field was not found")
     * @Assert\NotBlank(message="Person field can not be blank")
     * @JMS\Type("string")
     * @JMS\SerializedName("p")
     */
    private $personId;

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

    /**
     * @return string
     */
    public function getPersonId(): string
    {
        return $this->personId;
    }

    /**
     * @param string $personId
     */
    public function setPersonId(string $personId): void
    {
        $this->personId = $personId;
    }

}