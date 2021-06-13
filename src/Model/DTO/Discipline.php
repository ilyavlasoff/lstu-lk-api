<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Discipline
{
    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Идентификатор дисциплины", example="5:13293443")
     */
    public $id;

    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Наименование дисциплины", example="Теоретические основы автоматизированного управления")
     */
    public $name;

    /**
     * @var \App\Model\DTO\Chair | null
     * @OA\Property(ref=@Model(type=Chair::class))
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