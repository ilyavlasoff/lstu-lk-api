<?php

namespace App\Model\QueryParam;

use App\Model\DTO\Attachment;
use App\Model\DTO\ExternalLink;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class SendingPrivateMessage
{
    /**
     * @var string | null
     * @JMS\Type("string")
     * @Assert\NotNull(message="Message text was not found")
     * @Assert\NotBlank(message="Message text can not be empty")
     * @Assert\Length(max="2048", maxMessage="Message is too long, max length is {{ limit }} symbols, given {{ value }}")
     */
    private $message;

    /**
     * @var Attachment[]
     * @JMS\Type("array<App\Model\DTO\Attachment>")
     */
    private $attachments;

    /**
     * @var ExternalLink[]
     * @JMS\Type("array<App\Model\DTO\ExternalLink>")
     */
    private $extLinks;

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return Attachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param Attachment[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * @return ExternalLink[]
     */
    public function getExtLinks(): array
    {
        return $this->extLinks;
    }

    /**
     * @param ExternalLink[] $extLinks
     */
    public function setExtLinks(array $extLinks): void
    {
        $this->extLinks = $extLinks;
    }

}