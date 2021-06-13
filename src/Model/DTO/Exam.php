<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Exam
{
    /**
     * @var Discipline
     * @JMS\Type("App\Model\DTO\Discipline")
     * @OA\Property(ref=@Model(type=Discipline::class), description="Объект дисциплины", nullable=false)
     */
    private $discipline;

    /**
     * @var string
     * @OA\Property(type="string", description="ФИО экзаменатора", nullable=true, example="Домашнев П.А.")
     */
    private $teacherName;

    /**
     * @var \DateTime
     * @OA\Property(type="DateTime", description="Дата и время экзамена", nullable=true)
     */
    private $examTime;

    /**
     * @var string
     * @OA\Property(type="string", description="Наименование аудитории", nullable=true, example="362")
     */
    private $room;

    /**
     * @var string | null
     * @OA\Property(type="string", description="Корпус", nullable=true, example="2-ой корпус")
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