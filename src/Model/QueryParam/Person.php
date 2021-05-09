<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class Person
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Person identifier is was not found")
     * @Assert\NotBlank(message="Person identifier is empty")
     */
    private $personId;

    /**
     * @return string|null
     */
    public function getPersonId(): ?string
    {
        return $this->personId;
    }

    /**
     * @param string|null $personId
     */
    public function setPersonId(?string $personId): void
    {
        $this->personId = $personId;
    }

}