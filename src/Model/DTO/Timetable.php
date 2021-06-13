<?php

namespace App\Model\DTO;

use App\Model\DTO\Week;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Timetable
{
    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Идентификатор группы", example="5:34298534")
     */
    private $groupId;

    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Наименование группы", example="ПИ-17-1")
     */
    private $groupName;

    /**
     * @var Week[]
     * @OA\Property(type="array", @OA\Items(ref=@Model(type=Week::class)))
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