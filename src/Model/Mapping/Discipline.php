<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class Discipline
{
    /**
     * @var string
     * @JMS\Groups({"Definition"})
     */
    public $id;

    /**
     * @var string
     * @JMS\Groups({"Definition"})
     */
    public $name;

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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

}