<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class ProfilePicture
{
    /**
     * @var string
     * @OA\Property(type="string", nullable=true, description="Идентификатор персоны")
     */
    private $person;

    /**
     * @var string
     * @OA\Property(type="string", nullable=true, description="Base64 формат фото профиля")
     */
    private $profilePicture;

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
     * @return string
     */
    public function getProfilePicture(): string
    {
        return $this->profilePicture;
    }

    /**
     * @param string $profilePicture
     */
    public function setProfilePicture(string $profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }

}