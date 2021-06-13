<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class TimetableItem
{
    /**
     * @var Discipline|null
     * @JMS\Type("App\Model\DTO\Discipline")
     * @OA\Property(type="string", nullable=false, description="Идентификатор дисциплины", example="5:9283743")
     */
    private $discipline;

    /**
     * @var Teacher|null
     * @JMS\Type("App\Model\DTO\Teacher")
     * @OA\Property(ref=@Model(type=Teacher::class))
     */
    private $teacher;

    /**
     * @var string|null
     * @OA\Property(type="string", nullable=true, description="Тип учебного занятия", example="Практики")
     */
    private $lessonType;

    /**
     * @var int | null
     * @OA\Property(type="int", nullable=true, description="Номер пары в расписании", example="3")
     */
    private $lessonNumber;

    /**
     * @var string|null
     * @OA\Property(type="string", nullable=true, description="Время начала пары", example="08:00")
     */
    private $beginTime;

    /**
     * @var string|null
     * @OA\Property(type="string", nullable=true, description="Время окончания пары", example="09:40")
     */
    private $endTime;

    /**
     * @var string|null
     * @OA\Property(type="string", nullable=true, description="Аудитория проведения занятия", example="362")
     */
    private $room;

    /**
     * @var string|null
     * @OA\Property(type="string", nullable=true, description="Учебный корпус", example="2-й корпус")
     */
    private $campus;

    /**
     * @return Discipline|null
     */
    public function getDiscipline(): ?Discipline
    {
        return $this->discipline;
    }

    /**
     * @param Discipline|null $discipline
     */
    public function setDiscipline(?Discipline $discipline): void
    {
        $this->discipline = $discipline;
    }

    /**
     * @return Teacher|null
     */
    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    /**
     * @param Teacher|null $teacher
     */
    public function setTeacher(?Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return string|null
     */
    public function getLessonType(): ?string
    {
        return $this->lessonType;
    }

    /**
     * @param string|null $lessonType
     */
    public function setLessonType(?string $lessonType): void
    {
        $this->lessonType = $lessonType;
    }

    /**
     * @return int|null
     */
    public function getLessonNumber(): ?int
    {
        return $this->lessonNumber;
    }

    /**
     * @param int|null $lessonNumber
     */
    public function setLessonNumber(?int $lessonNumber): void
    {
        $this->lessonNumber = $lessonNumber;
    }

    /**
     * @return string|null
     */
    public function getBeginTime(): ?string
    {
        return $this->beginTime;
    }

    /**
     * @param string|null $beginTime
     */
    public function setBeginTime(?string $beginTime): void
    {
        $this->beginTime = $beginTime;
    }

    /**
     * @return string|null
     */
    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    /**
     * @param string|null $endTime
     */
    public function setEndTime(?string $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return string|null
     */
    public function getRoom(): ?string
    {
        return $this->room;
    }

    /**
     * @param string|null $room
     */
    public function setRoom(?string $room): void
    {
        $this->room = $room;
    }

    /**
     * @return string|null
     */
    public function getCampus(): ?string
    {
        return $this->campus;
    }

    /**
     * @param string|null $campus
     */
    public function setCampus(?string $campus): void
    {
        $this->campus = $campus;
    }

}