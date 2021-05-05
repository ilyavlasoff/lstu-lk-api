<?php

namespace App\Model\Response;

class ListedResponse
{
    /**
     * @var int | null
     */
    private $count;

    /**
     * @var int | null
     */
    private $offset;

    /**
     * @var int | null
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
    public function getNextOffset(): ?int
    {
        return $this->nextOffset;
    }

    /**
     * @param int|null $nextOffset
     */
    public function setNextOffset(?int $nextOffset): void
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