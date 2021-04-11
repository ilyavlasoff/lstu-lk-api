<?php

namespace App\Model\Response;

use App\Model\Mapping\Education;
use JMS\Serializer\Annotation as JMS;

class EducationsList
{
    /**
     * @var string
     */
    private $person;

    /**
     * @var bool|null
     */
    private $current;

    /**
     * @var Education[]
     * @JMS\Type("array<App\Model\Mapping\Education>")
     */
    private $educations;

    /**
     * @return string
     */
    public function getPerson(): string
    {
        return $this->person;
    }

    /**
     * @param string $person
     */
    public function setPerson(string $person): void
    {
        $this->person = $person;
    }

    /**
     * @return bool|null
     */
    public function getCurrent(): ?bool
    {
        return $this->current;
    }

    /**
     * @param bool|null $current
     */
    public function setCurrent(?bool $current): void
    {
        $this->current = $current;
    }

    /**
     * @return Education[]
     */
    public function getEducations(): array
    {
        return $this->educations;
    }

    /**
     * @param Education[] $educations
     */
    public function setEducations(array $educations): void
    {
        $this->educations = $educations;
    }

}