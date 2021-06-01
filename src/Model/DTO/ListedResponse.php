<?php

namespace App\Model\DTO;

class ListedResponse
{
    /**
     * @var int | null
     */
    private $count;

    /**
     * @var int | string | null
     */
    private $offset;

    /**
     * @var int | string | null
     */
    private $nextOffset;

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
     * @return int|string|null
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int|string|null $offset
     */
    public function setOffset($offset): void
    {
        $this->offset = $offset;
    }

    /**
     * @return int|string|null
     */
    public function getNextOffset()
    {
        return $this->nextOffset;
    }

    /**
     * @param int|string|null $nextOffset
     */
    public function setNextOffset($nextOffset): void
    {
        $this->nextOffset = $nextOffset;
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