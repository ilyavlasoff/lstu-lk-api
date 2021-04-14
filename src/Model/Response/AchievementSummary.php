<?php

namespace App\Model\Response;

use App\Model\Mapping\Achievement;
use App\Model\Mapping\Publication;

class AchievementSummary
{
    /**
     * @var int
     */
    private $achievementsTotalCount;

    /**
     * @var int
     */
    private $publicationsTotalCount;

    /**
     * @var Achievement[]
     */
    private $achievementList;

    /**
     * @var Publication[]
     */
    private $publicationsList;

    /**
     * @return int
     */
    public function getAchievementsTotalCount(): int
    {
        return $this->achievementsTotalCount;
    }

    /**
     * @param int $achievementsTotalCount
     */
    public function setAchievementsTotalCount(int $achievementsTotalCount): void
    {
        $this->achievementsTotalCount = $achievementsTotalCount;
    }

    /**
     * @return int
     */
    public function getPublicationsTotalCount(): int
    {
        return $this->publicationsTotalCount;
    }

    /**
     * @param int $publicationsTotalCount
     */
    public function setPublicationsTotalCount(int $publicationsTotalCount): void
    {
        $this->publicationsTotalCount = $publicationsTotalCount;
    }

    /**
     * @return Achievement[]
     */
    public function getAchievementList(): array
    {
        return $this->achievementList;
    }

    /**
     * @param Achievement[] $achievementList
     */
    public function setAchievementList(array $achievementList): void
    {
        $this->achievementList = $achievementList;
    }

    /**
     * @return Publication[]
     */
    public function getPublicationsList(): array
    {
        return $this->publicationsList;
    }

    /**
     * @param Publication[] $publicationsList
     */
    public function setPublicationsList(array $publicationsList): void
    {
        $this->publicationsList = $publicationsList;
    }

}