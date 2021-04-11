<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class TimetableItem
{
    /**
     * @var Discipline
     * @JMS\Type("App\Model\Mapping\Discipline")
     */
    private $discipline;

    /**
     * @var Teacher
     * @JMS\Type("App\Model\Mapping\Teacher")
     */
    private $teacher;

    /**
     * @var string
     */
    private $lessonType;

    /**
     * @var string
     */
    private $beginTime;

    /**
     * @var string
     */
    private $endTime;

    /**
     * @var string
     */
    private $room;

    /**
     * @var string
     */
    private $campus;

    /**
     * @return Discipline
     */
    public function getDiscipline(): Discipline
    {
        return $this->discipline;
    }

    /**
     * @param Discipline $discipline
     */
    public function setDiscipline(Discipline $discipline): void
    {
        $this->discipline = $discipline;
    }

    /**
     * @return Teacher
     */
    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    /**
     * @param Teacher $teacher
     */
    public function setTeacher(Teacher $teacher): void
    {
        $this->teacher = $teacher;
    }

    /**
     * @return string
     */
    public function getLessonType(): string
    {
        return $this->lessonType;
    }

    /**
     * @param string $lessonType
     */
    public function setLessonType(string $lessonType): void
    {
        $this->lessonType = $lessonType;
    }

    /**
     * @return string
     */
    public function getBeginTime(): string
    {
        return $this->beginTime;
    }

    /**
     * @param string $beginTime
     */
    public function setBeginTime(string $beginTime): void
    {
        $this->beginTime = $beginTime;
    }

    /**
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->endTime;
    }

    /**
     * @param string $endTime
     */
    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
    }

    /**
     * @return string
     */
    public function getRoom(): string
    {
        return $this->room;
    }

    /**
     * @param string $room
     */
    public function setRoom(string $room): void
    {
        $this->room = $room;
    }

    /**
     * @return string
     */
    public function getCampus(): string
    {
        return $this->campus;
    }

    /**
     * @param string $campus
     */
    public function setCampus(string $campus): void
    {
        $this->campus = $campus;
    }

}