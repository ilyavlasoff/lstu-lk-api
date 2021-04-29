<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class Discipline
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var \App\Model\Mapping\Chair | null
     */
    private $chair;

    /**
     * @var string | null
     */
    private $category;

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

    /**
     * @return \App\Model\Mapping\Chair|null
     */
    public function getChair(): ?Chair
    {
        return $this->chair;
    }

    /**
     * @param \App\Model\Mapping\Chair|null $chair
     */
    public function setChair(?Chair $chair): void
    {
        $this->chair = $chair;
    }

    /**
     * @return string|null
     */
    public function getCategory(): ?string
    {
        return $this->category;
    }

    /**
     * @param string|null $category
     */
    public function setCategory(?string $category): void
    {
        $this->category = $category;
    }

}