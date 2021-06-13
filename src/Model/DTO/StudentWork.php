<?php

namespace App\Model\DTO;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class StudentWork
{
    /**
     * @var string
     * @OA\Property(type="string", description="Идентификатор учебной работы", nullable=false, example="5:3243423")
     */
    private $id;

    /**
     * @var Teacher
     * @OA\Property(ref=@Model(type=Teacher::class), description="Объект преподавателя, добавившего задание", nullable=false)
     */
    private $teacher;

    /**
     * @var string | null
     * @OA\Property(type="string", description="Тип учебной работы", nullable=true, example="Практические работа")
     */
    private $workType;

    /**
     * @var string | null
     * @OA\Property(type="string", description="Наименование учебной работы", nullable=true, example="Лабораторная №1")
     */
    private $workName;

    /**
     * @var string | null
     * @OA\Property(type="string", description="Тема учебной работы", nullable=true, example="Методы оптимизации")
     */
    private $workTheme;

    /**
     * @var float | null
     * @OA\Property(type="float", description="Максимальная оценка учебной работы", nullable=true, example="10.0")
     */
    private $workMaxScore;

    /**
     * @var WorkAnswer | null
     * @OA\Property(ref=@Model(type=WorkAnswer::class), description="Список публикаций", nullable=false)
     */
    private $answer;

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
     * @return string|null
     */
    public function getWorkType(): ?string
    {
        return $this->workType;
    }

    /**
     * @param string|null $workType
     */
    public function setWorkType(?string $workType): void
    {
        $this->workType = $workType;
    }

    /**
     * @return string|null
     */
    public function getWorkName(): ?string
    {
        return $this->workName;
    }

    /**
     * @param string|null $workName
     */
    public function setWorkName(?string $workName): void
    {
        $this->workName = $workName;
    }

    /**
     * @return string|null
     */
    public function getWorkTheme(): ?string
    {
        return $this->workTheme;
    }

    /**
     * @param string|null $workTheme
     */
    public function setWorkTheme(?string $workTheme): void
    {
        $this->workTheme = $workTheme;
    }

    /**
     * @return int|null
     */
    public function getWorkMaxScore(): ?int
    {
        return $this->workMaxScore;
    }

    /**
     * @param int|null $workMaxScore
     */
    public function setWorkMaxScore(?int $workMaxScore): void
    {
        $this->workMaxScore = $workMaxScore;
    }

    /**
     * @return WorkAnswer|null
     */
    public function getAnswer(): ?WorkAnswer
    {
        return $this->answer;
    }

    /**
     * @param WorkAnswer|null $answer
     */
    public function setAnswer(?WorkAnswer $answer): void
    {
        $this->answer = $answer;
    }

}