<?php

namespace App\Model\DTO;

class IdentifierBoundedListedResponse
{
    /**
     * @var int | null
     */
    private $count;

    /**
     * @var string | null
     */
    private $currentBound;

    /**
     * @var string | null
     */
    private $nextBound;

    /**
     * @var int | null
     */
    private $remains;

    /**
     * @var array
     */
    private $payload;

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

    /**
     * @return string|null
     */
    public function getCurrentBound(): ?string
    {
        return $this->currentBound;
    }

    /**
     * @param string|null $currentBound
     */
    public function setCurrentBound(?string $currentBound): void
    {
        $this->currentBound = $currentBound;
    }

    /**
     * @return string|null
     */
    public function getNextBound(): ?string
    {
        return $this->nextBound;
    }

    /**
     * @param string|null $nextBound
     */
    public function setNextBound(?string $nextBound): void
    {
        $this->nextBound = $nextBound;
    }

    /**
     * @return int|null
     */
    public function getRemains(): ?int
    {
        return $this->remains;
    }

    /**
     * @param int|null $remains
     */
    public function setRemains(?int $remains): void
    {
        $this->remains = $remains;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

}