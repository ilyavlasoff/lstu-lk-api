<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class Chair
{
    /**
     * @var string | null
     * @OA\Property(type="string", nullable=false, description="Идентификатор кафедры", example="5:2343244")
     */
    private $id;

    /**
     * @var string | null
     * @OA\Property(type="string", nullable=true, description="Наименование кафедры", example="Автоматизированных систем управления")
     */
    private $chairName;

    /**
     * @var string | null
     * @OA\Property(type="string", nullable=true, description="Краткое имя кафедры", example="АСУ")
     */
    private $chairNameAbbr;

    /**
     * @var \App\Model\DTO\Faculty | null
     */
    private $faculty;

    /**
     * @var \App\Model\DTO\Person | null
     */
    private $chairman;

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
    public function getChairName(): ?string
    {
        return $this->chairName;
    }

    /**
     * @param string|null $chairName
     */
    public function setChairName(?string $chairName): void
    {
        $this->chairName = $chairName;
    }

    /**
     * @return string|null
     */
    public function getChairNameAbbr(): ?string
    {
        return $this->chairNameAbbr;
    }

    /**
     * @param string|null $chairNameAbbr
     */
    public function setChairNameAbbr(?string $chairNameAbbr): void
    {
        $this->chairNameAbbr = $chairNameAbbr;
    }

    /**
     * @return \App\Model\DTO\Faculty|null
     */
    public function getFaculty(): ?Faculty
    {
        return $this->faculty;
    }

    /**
     * @param \App\Model\DTO\Faculty|null $faculty
     */
    public function setFaculty(?Faculty $faculty): void
    {
        $this->faculty = $faculty;
    }

    /**
     * @return \App\Model\DTO\Person|null
     */
    public function getChairman(): ?Person
    {
        return $this->chairman;
    }

    /**
     * @param \App\Model\DTO\Person|null $chairman
     */
    public function setChairman(?Person $chairman): void
    {
        $this->chairman = $chairman;
    }

}