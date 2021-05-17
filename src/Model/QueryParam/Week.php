<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Week
{
    private $timetableWeekTranslate = [
        'green' => 'Зеленая',
        'white' => 'Белая',
    ];

    /**
     * @var string | null
     * @Assert\Choice(choices={"green", "white"}, message="Undefined week name")
     * @JMS\Type("string")
     */
    private $weekCode;

    /**
     * @return string|null
     */
    public function getWeekCode(): ?string
    {
        return $this->weekCode;
    }

    /**
     * @param string|null $weekCode
     */
    public function setWeekCode(?string $weekCode): void
    {
        $this->weekCode = $weekCode;
    }

    /**
     * @return string
     */
    public function getWeekNameValue(): string
    {
        return $this->timetableWeekTranslate[$this->weekCode] ?? 'undefined';
    }

    /**
     * @param string|null $value
     */
    public function createByWeekNameValue(?string $value) {
        $this->weekCode = array_flip($this->timetableWeekTranslate)[$value] ?? null;
    }

}