<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;

class Semester
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $year;

    /**
     * @var string|null
     */
    private $season;

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
     * @return string|null
     */
    public function getYear(): ?string
    {
        return $this->year;
    }

    /**
     * @param string|null $year
     */
    public function setYear(?string $year): void
    {
        $this->year = $year;
    }

    /**
     * @return string|null
     */
    public function getSeason(): ?string
    {
        return $this->season;
    }

    /**
     * @param string|null $season
     */
    public function setSeason(?string $season): void
    {
        $this->season = $season;
    }


}