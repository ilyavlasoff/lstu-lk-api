<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;

class ProfilePicture
{
    /**
     * @var string
     */
    private $person;

    /**
     * @var string
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