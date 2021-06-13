<?php

namespace App\Model\DTO;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Dialog
{
    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Идентификатор диалога", example="5:45348454")
     */
    private $id;

    /**
     * @var \App\Model\DTO\Person
     * @OA\Property(ref=@Model(type=Person::class), description="Объект персоны собеседника", nullable=false)
     */
    private $companion;

    /**
     * @var bool
     * @OA\Property(type="boolean", nullable=false, description="Флаг наличия непрочитанных сообщений", example="true")
     */
    private $hasUnread;

    /**
     * @var int
     * @OA\Property(type="integer", nullable=true, description="Количество непрочитанных сообщений", example="5")
     */
    private $unreadCount;

    /**
     * @var \App\Model\DTO\PrivateMessage
     * @OA\Property(ref=@Model(type=PrivateMessage::class), description="Объект последнего личного сообщения диалога", nullable=false)
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