<?php

namespace App\Model\Response;

use App\Model\Grouping\Week;

class Timetable
{
    /**
     * @var string
     */
    private $groupId;

    /**
     * @var string
     */
    private $groupName;

    /**
     * @var Week[]
     */
    private $weeks;

    /**
     * @return string
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     */
    public function setGroupId(string $groupId): void
    {
        $this->groupId = $groupId;
    }

    /**
     * @return string
     */
    public function getGroupName(): string
    {
        return $this->groupName;
    }

    /**
     * @param string $groupName
     */
    public function setGroupName(string $groupName): void
    {
        $this->groupName = $groupName;
    }

    /**
     * @return Week[]
     */
    public function getWeeks(): array
    {
        return $this->weeks;
    }

    /**
     * @param Week[] $weeks
     */
    public function setWeeks(array $weeks): void
    {
        $this->weeks = $weeks;
    }

}