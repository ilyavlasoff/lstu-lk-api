<?php

namespace App\Model\Response;

use App\Model\Mapping\Exam;
use JMS\Serializer\Annotation as JMS;

class ExamsTimetable
{
    /**
     * @var string
     */
    private $edu;

    /**
     * @var string
     */
    private $sem;

    /**
     * @var Exam[]
     * @JMS\Type("array<App\Model\Mapping\Exam>")
     */
    private $exams;

    /**
     * @return string
     */
    public function getEdu(): string
    {
        return $this->edu;
    }

    /**
     * @param string $edu
     */
    public function setEdu(string $edu): void
    {
        $this->edu = $edu;
    }

    /**
     * @return string
     */
    public function getSem(): string
    {
        return $this->sem;
    }

    /**
     * @param string $sem
     */
    public function setSem(string $sem): void
    {
        $this->sem = $sem;
    }

    /**
     * @return Exam[]
     */
    public function getExams(): array
    {
        return $this->exams;
    }

    /**
     * @param Exam[] $exams
     */
    public function setExams(array $exams): void
    {
        $this->exams = $exams;
    }

}