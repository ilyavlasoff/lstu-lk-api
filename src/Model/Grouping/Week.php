<?php

namespace App\Model\Grouping;

use JMS\Serializer\Annotation as JMS;

class Week
{
    /**
     * @var bool | null
     */
    private $current;

    /**
     * @var string | null
     */
    private $type;

    /**
     * @var Day[]
     * @JMS\Type("array<App\Model\Grouping\Day>")
     */
    private $days;

    /**
     * @return bool|null
     */
    public function getCurrent(): ?bool
    {
        return $this->current;
    }

    /**
     * @param bool|null $current
     */
    public function setCurrent(?bool $current): void
    {
        $this->current = $current;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return Day[]
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @param Day[] $days
     */
    public function setDays(array $days): void
    {
        $this->days = $days;
    }

}