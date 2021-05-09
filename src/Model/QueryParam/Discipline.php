<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class Discipline
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Discipline field was not found")
     * @Assert\NotBlank(message="Disciplien field can not be blank")
     */
    private $disciplineId;

    /**
     * @return string|null
     */
    public function getDisciplineId(): ?string
    {
        return $this->disciplineId;
    }

    /**
     * @param string|null $disciplineId
     */
    public function setDisciplineId(?string $disciplineId): void
    {
        $this->disciplineId = $disciplineId;
    }

}