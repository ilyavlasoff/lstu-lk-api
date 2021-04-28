<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class Person
{
    /**
     * @var string
     * @Assert\NotNull(message="Person identifier is was not found")
     * @Assert\NotBlank(message="Person identifier is empty")
     */
    private $personId;

    /**
     * @return string
     */
    public function getPersonId(): string
    {
        return $this->personId;
    }

    /**
     * @param string $personId
     */
    public function setPersonId(string $personId): void
    {
        $this->personId = $personId;
    }

}