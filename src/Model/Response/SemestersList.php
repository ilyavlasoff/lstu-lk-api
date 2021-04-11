<?php

namespace App\Model\Response;

use App\Model\Mapping\Semester;

class SemestersList
{
    /**
     * @var string
     */
    private $education;

    /**
     * @var bool
     */
    private $current;

    /**
     * @var Semester[]
     */
    private $semesters;

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
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->current;
    }

    /**
     * @param bool $current
     */
    public function setCurrent(bool $current): void
    {
        $this->current = $current;
    }

    /**
     * @return Semester[]
     */
    public function getSemesters(): array
    {
        return $this->semesters;
    }

    /**
     * @param Semester[] $semesters
     */
    public function setSemesters(array $semesters): void
    {
        $this->semesters = $semesters;
    }

}