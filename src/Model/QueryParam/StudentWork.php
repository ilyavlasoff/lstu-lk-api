<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class StudentWork
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Work identifier not found")
     * @Assert\NotBlank(message="Work identifier can not be blank")
     */
    private $work;

    /**
     * @return string|null
     */
    public function getWork(): ?string
    {
        return $this->work;
    }

    /**
     * @param string|null $work
     */
    public function setWork(?string $work): void
    {
        $this->work = $work;
    }

}