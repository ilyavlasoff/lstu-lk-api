<?php

namespace App\Model\Mapping;

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
     * @JMS\Type("DateTime<'Y-m-d\TH:i:s.uT'>")
     */
    private $created;

    /**
     * @var string | null
     */
    private $msg;

    /**
     * @var \App\Model\Mapping\Attachment[]
     */
    private $attachments;

    /**
     * @var \App\Model\Mapping\ExternalLink[]
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

    /**
     * @return \App\Model\Mapping\ExternalLink[]
     */
    public function getExternalLinks(): array
    {
        return $this->externalLinks;
    }

    /**
     * @param \App\Model\Mapping\ExternalLink[] $externalLinks
     */
    public function setExternalLinks(array $externalLinks): void
    {
        $this->externalLinks = $externalLinks;
    }

}