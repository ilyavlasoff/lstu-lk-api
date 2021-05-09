<?php

namespace App\Model\DTO;

use App\Model\DTO\TimetableItem;
use JMS\Serializer\Annotation as JMS;

class Day
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string | null
     */
    private $name;

    /**
     * @var integer | null
     */
    private $number;

    /**
     * @var TimetableItem[]
     */
    private $lessons;

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
     * @return int|null
     */
    public function getNumber(): ?int
    {
        return $this->number;
    }

    /**
     * @param int|null $number
     */
    public function setNumber(?int $number): void
    {
        $this->number = $number;
    }

    /**
     * @return TimetableItem[]
     */
    public function getLessons(): array
    {
        return $this->lessons;
    }

    /**
     * @param TimetableItem[] $lessons
     */
    public function setLessons(array $lessons): void
    {
        $this->lessons = $lessons;
    }

}