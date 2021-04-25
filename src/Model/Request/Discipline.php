<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Discipline
{
    /**
     * @var string
     * @Assert\NotNull(message="Discipline field was not found")
     * @Assert\NotBlank(message="Disciplien field can not be blank")
     * @JMS\Type("string")
     * @JMS\SerializedName("dis")
     */
    private $disciplineId;

    /**
     * @return string
     */
    public function getDisciplineId(): string
    {
        return $this->disciplineId;
    }

    /**
     * @param string $disciplineId
     */
    public function setDisciplineId(string $disciplineId): void
    {
        $this->disciplineId = $disciplineId;
    }

}