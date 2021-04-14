<?php

namespace App\Model\Response;

use App\Model\Mapping\Publication;

class PublicationList
{
    /**
     * @var string
     */
    private $person;

    /**
     * @var int
     */
    private $remain;

    /**
     * @var Publication[]
     */
    private $publications;

    /**
     * @return string
     */
    public function getPerson(): string
    {
        return $this->person;
    }

    /**
     * @param string $person
     */
    public function setPerson(string $person): void
    {
        $this->person = $person;
    }

    /**
     * @return int
     */
    public function getRemain(): int
    {
        return $this->remain;
    }

    /**
     * @param int $remain
     */
    public function setRemain(int $remain): void
    {
        $this->remain = $remain;
    }

    /**
     * @return Publication[]
     */
    public function getPublications(): array
    {
        return $this->publications;
    }

    /**
     * @param Publication[] $publications
     */
    public function setPublications(array $publications): void
    {
        $this->publications = $publications;
    }

}