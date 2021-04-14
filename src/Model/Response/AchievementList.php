<?php

namespace App\Model\Response;

use App\Model\Mapping\Achievement;

class AchievementList
{
    /**
     * @var string
     */
    private $person;

    /**
     * @var int
     */
    private $remain;

    /**
     * @var Achievement[]
     */
    private $achievements;

    /**
     * @return string
     */
    public function getPerson(): string
    {
        return $this->person;
    }

    /**
     * @param string $person
     */
    public function setPerson(string $person): void
    {
        $this->person = $person;
    }

    /**
     * @return int
     */
    public function getRemain(): int
    {
        return $this->remain;
    }

    /**
     * @param int $remain
     */
    public function setRemain(int $remain): void
    {
        $this->remain = $remain;
    }

    /**
     * @return Achievement[]
     */
    public function getAchievements(): array
    {
        return $this->achievements;
    }

    /**
     * @param Achievement[] $achievements
     */
    public function setAchievements(array $achievements): void
    {
        $this->achievements = $achievements;
    }

}