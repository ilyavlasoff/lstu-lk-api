<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class Semester
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Semester field was not found")
     * @Assert\NotBlank(message="Semester field can not be empty")
     */
    private $semesterId;

    /**
     * @return string|null
     */
    public function getSemesterId(): ?string
    {
        return $this->semesterId;
    }

    /**
     * @param string|null $semesterId
     */
    public function setSemesterId(?string $semesterId): void
    {
        $this->semesterId = $semesterId;
    }

}