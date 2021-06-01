<?php

namespace App\Model\QueryParam;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class IdentifierPaginator
{
    /**
     * @var string | null
     * @JMS\Type("string")
     */
    private $edge;

    /**
     * @var int | null
     * @JMS\Type("string")
     * @Assert\GreaterThan(value=0, message="Object count must be above zero")
     */
    private $count;

    /**
     * @return string|null
     */
    public function getEdge(): ?string
    {
        return $this->edge;
    }

    /**
     * @param string|null $edge
     */
    public function setEdge(?string $edge): void
    {
        $this->edge = $edge;
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