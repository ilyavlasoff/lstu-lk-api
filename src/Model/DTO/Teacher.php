<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Teacher
{
    /**
     * @var string | null
     * @OA\Property(type="string", description="Идентификато преподавателя", nullable=false, example="5:341243")
     */
    private $id;

    /**
     * @var Person | null
     * @JMS\Type("App\Model\DTO\Person")
     * @OA\Property(ref=@Model(type=Person::class), nullable=false, description="Персона преподавателя")
     */
    private $person;

    /**
     * @var string | null
     * @OA\Property(type="string", nullable=true, description="Занимаемая должность", example="Декан ФАИ")
     */
    private $position;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return Person|null
     */
    public function getPerson(): ?Person
    {
        return $this->person;
    }

    /**
     * @param Person|null $person
     */
    public function setPerson(?Person $person): void
    {
        $this->person = $person;
    }

    /**
     * @return string|null
     */
    public function getPosition(): ?string
    {
        return $this->position;
    }

    /**
     * @param string|null $position
     */
    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

}