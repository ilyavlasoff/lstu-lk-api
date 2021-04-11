<?php

namespace App\Model\Grouping;

use JMS\Serializer\Annotation as JMS;

class Week
{
    /**
     * @var bool
     */
    private $current;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Day[]
     * @JMS\Type("array<App\Model\Grouping\Day>")
     */
    private $days;

    /**
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->current;
    }

    /**
     * @param bool $current
     */
    public function setCurrent(bool $current): void
    {
        $this->current = $current;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
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