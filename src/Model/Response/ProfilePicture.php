<?php

namespace App\Model\Response;

use JMS\Serializer\Annotation as JMS;

class ProfilePicture
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var string
     * @JMS\Type("string")
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