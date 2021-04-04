<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class AcademicSubject
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    private $subjectId;
    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $subjectName;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $chairName;

    /**
     * @return string|null
     */
    public function getSubjectName(): ?string
    {
        return $this->subjectName;
    }

    /**
     * @param string|null $subjectName
     */
    public function setSubjectName(?string $subjectName): void
    {
        $this->subjectName = $subjectName;
    }

    /**
     * @return string|null
     */
    public function getChairName(): ?string
    {
        return $this->chairName;
    }

    /**
     * @param string|null $chairName
     */
    public function setChairName(?string $chairName): void
    {
        $this->chairName = $chairName;
    }

    /**
     * @return string
     */
    public function getSubjectId(): string
    {
        return $this->subjectId;
    }

    /**
     * @param string $subjectId
     */
    public function setSubjectId(string $subjectId): void
    {
        $this->subjectId = $subjectId;
    }

}