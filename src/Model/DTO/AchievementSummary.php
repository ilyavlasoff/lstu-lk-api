<?php

namespace App\Model\DTO;

use App\Model\DTO\Achievement;
use App\Model\DTO\Publication;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;
use JMS\Serializer\Annotation as JMS;

class AchievementSummary
{
    /**
     * @var int
     * @OA\Property(type="integer", description="Общее количество достижений пользователя", nullable=false, example="3")
     */
    private $achievementsTotalCount;

    /**
     * @var int
     * @OA\Property(type="integer", description="Общее количество публикаций пользователя", nullable=false, example="2")
     */
    private $publicationsTotalCount;

    /**
     * @var Achievement[]
     * @OA\Property(ref=@Model(type=Achievement::class), description="Список достижений", nullable=false)
     */
    private $achievementList;

    /**
     * @var Publication[]
     * @OA\Property(ref=@Model(type=Publication::class), description="Список публикаций", nullable=false)
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