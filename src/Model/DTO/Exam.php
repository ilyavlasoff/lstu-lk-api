<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;

class Exam
{
    /**
     * @var Discipline
     * @JMS\Type("App\Model\DTO\Discipline")
     */
    private $discipline;

    /**
     * @var string
     */
    private $teacherName;

    /**
     * @var \DateTime
     */
    private $examTime;

    /**
     * @var string
     */
    private $room;

    /**
     * @var string | null
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
     * @return string
     */
    public function getTeacherName(): string
    {
        return $this->teacherName;
    }

    /**
     * @param string $teacherName
     */
    public function setTeacherName(string $teacherName): void
    {
        $this->teacherName = $teacherName;
    }

    /**
     * @return \DateTime
     */
    public function getExamTime(): \DateTime
    {
        return $this->examTime;
    }

    /**
     * @param \DateTime $examTime
     */
    public function setExamTime(\DateTime $examTime): void
    {
        $this->examTime = $examTime;
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