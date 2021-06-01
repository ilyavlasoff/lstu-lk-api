<?php

namespace App\Model\QueryParam;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class OrderMode
{
    /**
     * @var string | null
     * @JMS\Type("string")
     * @Assert\Choice({"ef", "lf"})
     */
    private $orderBy;

    /**
     * @return string|null
     */
    public function getOrderBy(): ?string
    {
        return $this->orderBy;
    }

    /**
     * @param string|null $orderBy
     */
    public function setOrderBy(?string $orderBy): void
    {
        $this->orderBy = $orderBy;
    }


}