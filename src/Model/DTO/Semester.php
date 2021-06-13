<?php

namespace App\Model\DTO;

use OpenApi\Annotations as OA;
use JMS\Serializer\Annotation as JMS;

class Semester
{
    /**
     * @var string
     * @OA\Property(type="string", description="Идентификатор семестра", nullable=false, example="5:23453423")
     */
    private $id;

    /**
     * @var string|null
     * @OA\Property(type="string", description="Год обучения", nullable=true, example="2020")
     */
    private $year;

    /**
     * @var string|null
     * @OA\Property(type="string", description="Тип семестра", nullable=true, example="Весна")
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