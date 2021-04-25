<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Semester
{
    /**
     * @var string
     * @Assert\NotNull(message="Semester field was not found")
     * @Assert\NotBlank(message="Semester field can not be empty")
     * @JMS\SerializedName("sem")
     * @JMS\Type("string")
     */
    private $semesterId;

    /**
     * @return string
     */
    public function getSemesterId(): string
    {
        return $this->semesterId;
    }

    /**
     * @param string $semesterId
     */
    public function setSemesterId(string $semesterId): void
    {
        $this->semesterId = $semesterId;
    }

}