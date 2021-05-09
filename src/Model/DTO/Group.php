<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;

class Group
{
    /**
     * @var string | null
     */
    private $id;

    /**
     * @var string | null
     */
    private $name;

    /**
     * @var \App\Model\DTO\Chair | null
     */
    private $chair;

    /**
     * @var \DateTime|null
     */
    private $admission;

    /**
     * @var \DateTime|null
     */
    private $graduation;

    /**
     * @var \App\Model\DTO\Speciality | null
     */
    private $speciality;

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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return \App\Model\DTO\Chair|null
     */
    public function getChair(): ?Chair
    {
        return $this->chair;
    }

    /**
     * @param \App\Model\DTO\Chair|null $chair
     */
    public function setChair(?Chair $chair): void
    {
        $this->chair = $chair;
    }

    /**
     * @return \DateTime|null
     */
    public function getAdmission(): ?\DateTime
    {
        return $this->admission;
    }

    /**
     * @param \DateTime|null $admission
     */
    public function setAdmission(?\DateTime $admission): void
    {
        $this->admission = $admission;
    }

    /**
     * @return \DateTime|null
     */
    public function getGraduation(): ?\DateTime
    {
        return $this->graduation;
    }

    /**
     * @param \DateTime|null $graduation
     */
    public function setGraduation(?\DateTime $graduation): void
    {
        $this->graduation = $graduation;
    }

    /**
     * @return \App\Model\DTO\Speciality|null
     */
    public function getSpeciality(): ?Speciality
    {
        return $this->speciality;
    }

    /**
     * @param \App\Model\DTO\Speciality|null $speciality
     */
    public function setSpeciality(?Speciality $speciality): void
    {
        $this->speciality = $speciality;
    }

}