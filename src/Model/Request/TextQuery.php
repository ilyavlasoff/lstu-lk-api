<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class TextQuery
{
    /**
     * @var string | null
     * @Assert\Length(min=3, minMessage="Query must has at least {{ limit }} symbols")
     */
    private $queryString;

    /**
     * @return string|null
     */
    public function getQueryString(): ?string
    {
        return $this->queryString;
    }

    /**
     * @param string|null $queryString
     */
    public function setQueryString(?string $queryString): void
    {
        $this->queryString = $queryString;
    }

}