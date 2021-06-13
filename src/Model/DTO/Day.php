<?php

namespace App\Model\DTO;

use App\Model\DTO\TimetableItem;
use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Day
{
    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Идентификатор учебного дня", example="5:1324234")
     */
    private $id;

    /**
     * @var string | null
     * @OA\Property(type="string", nullable=true, description="Наименование учебного дня", example="Понедельник")
     */
    private $name;

    /**
     * @var integer | null
     * @OA\Property(type="int", nullable=true, description="Номер учебного дня в неделе", example="1")
     */
    private $number;

    /**
     * @var TimetableItem[]
     * @OA\Property(ref=@Model(type=TimetableItem::class))
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