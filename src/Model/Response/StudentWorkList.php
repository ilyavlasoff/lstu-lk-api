<?php

namespace App\Model\Response;

use App\Model\Mapping\StudentWork;

class StudentWorkList
{
    /**
     * @var string
     */
    private $discipline;

    /**
     * @var string
     */
    private $semester;

    /**
     * @var string
     */
    private $education;

    /**
     * @var StudentWork[]
     */
    private $tasks;

    /**
     * @return string
     */
    public function getDiscipline(): string
    {
        return $this->discipline;
    }

    /**
     * @param string $discipline
     */
    public function setDiscipline(string $discipline): void
    {
        $this->discipline = $discipline;
    }

    /**
     * @return string
     */
    public function getSemester(): string
    {
        return $this->semester;
    }

    /**
     * @param string $semester
     */
    public function setSemester(string $semester): void
    {
        $this->semester = $semester;
    }

    /**
     * @return string
     */
    public function getEducation(): string
    {
        return $this->education;
    }

    /**
     * @param string $education
     */
    public function setEducation(string $education): void
    {
        $this->education = $education;
    }

    /**
     * @return StudentWork[]
     */
    public function getTasks(): array
    {
        return $this->tasks;
    }

    /**
     * @param StudentWork[] $tasks
     */
    public function setTasks(array $tasks): void
    {
        $this->tasks = $tasks;
    }

}