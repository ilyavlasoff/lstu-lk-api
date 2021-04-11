<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class Semester
{
    /**
     * @var string
     * @JMS\Type("string")
     * @JMS\Groups("Default", "idOnly")
     */
    private $oid;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $year;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $season;

    /**
     * @return string
     */
    public function getOid(): string
    {
        return $this->oid;
    }

    /**
     * @param string $oid
     */
    public function setOid(string $oid): void
    {
        $this->oid = $oid;
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