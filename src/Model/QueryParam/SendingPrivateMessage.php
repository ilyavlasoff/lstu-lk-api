<?php

namespace App\Model\QueryParam;

use App\Model\DTO\Attachment;
use App\Model\DTO\ExternalLink;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class SendingPrivateMessage
{
    /**
     * @var string | null
     * @JMS\Type("string")
     * @JMS\SerializedName("message_text")
     * @Assert\NotNull(message="Message text was not found")
     * @Assert\NotBlank(message="Message text can not be empty")
     * @Assert\Length(max="2048", maxMessage="Message is too long, max length is {{ limit }} symbols, given {{ value }}")
     * @OA\Property(type="string", nullable=false, description="Текст добавляемого личного сообщения", example="Добрый день!")
     */
    private $message;

    /**
     * @var Attachment[]
     * @JMS\Type("array<App\Model\DTO\Attachment>")
     * @OA\Property(ref=@Model(type=Attachment::class), description="Список прикрепленных документов", nullable=false)
     */
    private $attachments;

    /**
     * @var ExternalLink[]
     * @JMS\Type("array<App\Model\DTO\ExternalLink>")
     * @OA\Property(ref=@Model(type=ExternalLink::class), description="Список внешних ссылок, прикрепленных к сообщению", nullable=false)
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
        return $this->attachments ?? [];
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
        return $this->extLinks ?? [];
    }

    /**
     * @param ExternalLink[] $extLinks
     */
    public function setExtLinks(array $extLinks): void
    {
        $this->extLinks = $extLinks;
    }

}