<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Person
{
    /**
     * @var string
     * @Assert\NotNull(message="Person identifier is was not found")
     * @Assert\NotBlank(message="Person identifier is empty")
     * @JMS\SerializedName("p")
     * @JMS\Type("string")
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