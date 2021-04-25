<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Education
{
    /**
     * @var string
     * @Assert\NotNull(message="Education field was not found")
     * @Assert\NotBlank(message="Education field can not be empty")
     * @JMS\SerializedName("edu")
     * @JMS\Type("string")
     */
    private $educationId;

    /**
     * @return string
     */
    public function getEducationId(): string
    {
        return $this->educationId;
    }

    /**
     * @param string $educationId
     */
    public function setEducationId(string $educationId): void
    {
        $this->educationId = $educationId;
    }

}