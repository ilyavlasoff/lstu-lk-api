<?php

namespace App\Model\Grouping;

use App\Model\Mapping\TimetableItem;

class Day
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     * @JMS\Groups({"lessonsContainer"})
     */
    private $name;

    /**
     * @var integer
     * @JMS\Groups({"lessonsContainer"})
     */
    private $number;

    /**
     * @var TimetableItem[]
     * @JMS\Groups({"lessonsContainer"})
     */
    private $lessons;

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
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }

    /**
     * @param int $number
     */
    public function setNumber(int $number): void
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

}