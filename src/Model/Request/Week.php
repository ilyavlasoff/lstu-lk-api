<?php

namespace App\Model\Request;

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
     * @JMS\SerializedName("week")
     * @JMS\Accessor(getter="weekNameToCode", setter="weekCodeToName")
     * @JMS\Type("string")
     */
    private $weekName;

    /**
     * @return string|null
     */
    public function getWeekName(): ?string
    {
        return $this->weekName;
    }

    /**
     * @param string|null $weekName
     */
    public function setWeekName(?string $weekName): void
    {
        $this->weekName = $weekName;
    }

    public function weekCodeToName(?string $weekCode)
    {
        $this->weekName = $this->timetableWeekTranslate[$weekCode] ?? 'undefined';
    }

    public function weekNameToCode()
    {
        return (array_flip($this->timetableWeekTranslate))[$this->weekName] ?? null;
    }

}