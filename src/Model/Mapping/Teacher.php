<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class Teacher
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Person | null
     * @JMS\Type("App\Model\Mapping\Person")
     */
    private $person;

    /**
     * @var string | null
     */
    private $position;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     */
    public function setPerson(?Person $person): void
    {
        $this->person = $person;
    }

}