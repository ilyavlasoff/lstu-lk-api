<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;

class DiscussionMessage
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Person
     */
    private $sender;

    /**
     * @var \DateTime | null
     */
    private $created;

    /**
     * @var string | null
     */
    private $msg;

    /**
     * @var \App\Model\DTO\Attachment[]
     */
    private $attachments;

    /**
     * @var \App\Model\DTO\ExternalLink[]
     */
    private $externalLinks;

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
    public function getSender(): Person
    {
        return $this->sender;
    }

    /**
     * @param \App\Model\DTO\Person $sender
     */
    public function setSender(Person $sender): void
    {
        $this->sender = $sender;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreated(): ?\DateTime
    {
        return $this->created;
    }

    /**
     * @param \DateTime|null $created
     */
    public function setCreated(?\DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return string|null
     */
    public function getMsg(): ?string
    {
        return $this->msg;
    }

    /**
     * @param string|null $msg
     */
    public function setMsg(?string $msg): void
    {
        $this->msg = $msg;
    }

    /**
     * @return \App\Model\DTO\Attachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param \App\Model\DTO\Attachment[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * @return \App\Model\DTO\ExternalLink[]
     */
    public function getExternalLinks(): array
    {
        return $this->externalLinks;
    }

    /**
     * @param \App\Model\DTO\ExternalLink[] $externalLinks
     */
    public function setExternalLinks(array $externalLinks): void
    {
        $this->externalLinks = $externalLinks;
    }

}