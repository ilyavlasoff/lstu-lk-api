<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;

class Faculty
{
    /**
     * @var string | null
     */
    private $id;

    /**
     * @var string | null
     */
    private $facCode;

    /**
     * @var string | null
     */
    private $facName;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getFacCode(): ?string
    {
        return $this->facCode;
    }

    /**
     * @param string|null $facCode
     */
    public function setFacCode(?string $facCode): void
    {
        $this->facCode = $facCode;
    }

    /**
     * @return string|null
     */
    public function getFacName(): ?string
    {
        return $this->facName;
    }

    /**
     * @param string|null $facName
     */
    public function setFacName(?string $facName): void
    {
        $this->facName = $facName;
    }

}