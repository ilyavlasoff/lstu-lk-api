<?php

namespace App\Model\Mapping;

class StudentWork
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Teacher
     */
    private $teacher;

    /**
     * @var string | null
     */
    private $workType;

    /**
     * @var string | null
     */
    private $workName;

    /**
     * @var string | null
     */
    private $workTheme;

    /**
     * @var int | null
     */
    private $workMaxScore;

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

}