<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class Paginator
{
    /**
     * @var int | null
     * @Assert\GreaterThanOrEqual(value="0", message="Offset must me greater than or equals to 0")
     * @JMS\Type("int")
     * @JMS\SerializedName("of")
     */
    private $offset;

    /**
     * @var int | null
     * @Assert\GreaterThan(value="0", message="Count parameter must be greater than 0")
     * @JMS\Type("int")
     * @JMS\SerializedName("c")
     */
    private $count;

    /**
     * @return int|null
     */
    public function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @param int|null $offset
     */
    public function setOffset(?int $offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int|null
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int|null $count
     */
    public function setCount(?int $count): void
    {
        $this->count = $count;
    }

}