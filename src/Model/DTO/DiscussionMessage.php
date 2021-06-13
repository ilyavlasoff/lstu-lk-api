<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class DiscussionMessage
{
    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Идентификатор сообщения", example="5:2323434")
     */
    private $id;

    /**
     * @var Person
     * @OA\Property(ref=@Model(type=Person::class), nullable=false, description="Объект персоны отправителя")
     */
    private $sender;

    /**
     * @var \DateTime | null
     * @OA\Property(type="DateTime", nullable=true, description="Время отправки сообщения")
     */
    private $created;

    /**
     * @var string | null
     * @OA\Property(type="string", nullable=true, description="Текст сообщения", example="Всем привет!")
     */
    private $msg;

    /**
     * @var \App\Model\DTO\Attachment[]
     * @OA\Property(ref=@Model(type=Achievement::class))
     */
    private $attachments;

    /**
     * @var \App\Model\DTO\ExternalLink[]
     * @OA\Property(ref=@Model(type=ExternalLink::class))
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