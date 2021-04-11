<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class TimetableItem
{
    /**
     * @var Discipline|null
     * @JMS\Type("App\Model\Mapping\Discipline")
     */
    private $discipline;

    /**
     * @var Teacher|null
     * @JMS\Type("App\Model\Mapping\Teacher")
     */
    private $teacher;

    /**
     * @var string|null
     */
    private $lessonType;

    /**
     * @var string|null
     */
    private $beginTime;

    /**
     * @var string|null
     */
    private $endTime;

    /**
     * @var string|null
     */
    private $room;

    /**
     * @var string|null
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