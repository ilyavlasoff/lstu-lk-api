<?php

namespace App\Model\DTO;

class Dialog
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var \App\Model\DTO\Person
     */
    private $companion;

    /**
     * @var bool
     */
    private $hasUnread;

    /**
     * @var int
     */
    private $unreadCount;

    /**
     * @var \App\Model\DTO\PrivateMessage
     */
    private $lastMessage;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return \App\Model\DTO\Person
     */
    public function getCompanion(): Person
    {
        return $this->companion;
    }

    /**
     * @param \App\Model\DTO\Person $companion
     */
    public function setCompanion(Person $companion): void
    {
        $this->companion = $companion;
    }

    /**
     * @return bool
     */
    public function isHasUnread(): bool
    {
        return $this->hasUnread;
    }

    /**
     * @param bool $hasUnread
     */
    public function setHasUnread(bool $hasUnread): void
    {
        $this->hasUnread = $hasUnread;
    }

    /**
     * @return int
     */
    public function getUnreadCount(): int
    {
        return $this->unreadCount;
    }

    /**
     * @param int $unreadCount
     */
    public function setUnreadCount(int $unreadCount): void
    {
        $this->unreadCount = $unreadCount;
    }

    /**
     * @return \App\Model\DTO\PrivateMessage
     */
    public function getLastMessage(): PrivateMessage
    {
        return $this->lastMessage;
    }

    /**
     * @param \App\Model\DTO\PrivateMessage $lastMessage
     */
    public function setLastMessage(PrivateMessage $lastMessage): void
    {
        $this->lastMessage = $lastMessage;
    }

}