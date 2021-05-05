<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class Education
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Education field was not found")
     * @Assert\NotBlank(message="Education field can not be empty")
     */
    private $educationId;

    /**
     * @return string|null
     */
    public function getEducationId(): ?string
    {
        return $this->educationId;
    }

    /**
     * @param string|null $educationId
     */
    public function setEducationId(?string $educationId): void
    {
        $this->educationId = $educationId;
    }

}