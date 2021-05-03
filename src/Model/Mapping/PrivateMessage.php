<?php

namespace App\Model\Mapping;

class PrivateMessage
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $chat;

    /**
     * @var \App\Model\Mapping\Person
     */
    private $sender;

    /**
     * @var bool | null
     */
    private $meSender;

    /**
     * @var string | null
     */
    private $messageText;

    /**
     * @var \DateTime
     */
    private $sendTime;

    /**
     * @var bool | null
     */
    private $isRead;

    /**
     * @var \App\Model\Mapping\ExternalLink[]
     */
    private $links;

    /**
     * @var \App\Model\Mapping\Attachment[]
     */
    private $attachments;

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
     * @return string
     */
    public function getChat(): string
    {
        return $this->chat;
    }

    /**
     * @param string $chat
     */
    public function setChat(string $chat): void
    {
        $this->chat = $chat;
    }

    /**
     * @return \App\Model\Mapping\Person
     */
    public function getSender(): Person
    {
        return $this->sender;
    }

    /**
     * @param \App\Model\Mapping\Person $sender
     */
    public function setSender(Person $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return bool|null
     */
    public function getMeSender(): ?bool
    {
        return $this->meSender;
    }

    /**
     * @param bool|null $meSender
     */
    public function setMeSender(?bool $meSender): void
    {
        $this->meSender = $meSender;
    }

    /**
     * @return string|null
     */
    public function getMessageText(): ?string
    {
        return $this->messageText;
    }

    /**
     * @param string|null $messageText
     */
    public function setMessageText(?string $messageText): void
    {
        $this->messageText = $messageText;
    }

    /**
     * @return \DateTime
     */
    public function getSendTime(): \DateTime
    {
        return $this->sendTime;
    }

    /**
     * @param \DateTime $sendTime
     */
    public function setSendTime(\DateTime $sendTime): void
    {
        $this->sendTime = $sendTime;
    }

    /**
     * @return bool|null
     */
    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    /**
     * @param bool|null $isRead
     */
    public function setIsRead(?bool $isRead): void
    {
        $this->isRead = $isRead;
    }

    /**
     * @return \App\Model\Mapping\ExternalLink[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @param \App\Model\Mapping\ExternalLink[] $links
     */
    public function setLinks(array $links): void
    {
        $this->links = $links;
    }

    /**
     * @return \App\Model\Mapping\Attachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param \App\Model\Mapping\Attachment[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

}