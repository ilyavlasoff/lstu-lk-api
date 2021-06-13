<?php

namespace App\Model\QueryParam;

use App\Model\DTO\Attachment;
use App\Model\DTO\ExternalLink;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class SendingDiscussionMessage
{
    /**
     * @var string | null
     * @JMS\Type("string")
     * @Assert\NotNull(message="DisciplineDiscussionMessage must contain any text")
     * @Assert\NotBlank(message="DisciplineDiscussionMessage text can not be blank")
     * @Assert\Length(max=2048, maxMessage="Too long message, max length is {{ limit }}, given {{ value }} symbols")
     * @OA\Property(type="string", nullable=false, description="Текст сообщения", example="Всем привет")
     */
    private $msg;

    /**
     * @var Attachment[]
     * @JMS\Type("array<App\Model\DTO\Attachment>")
     * @OA\Property(ref=@Model(type=Attachment::class))
     */
    private $attachments = [];

    /**
     * @var ExternalLink[]
     * @JMS\Type("array<App\Model\DTO\ExternalLink>")
     * @OA\Property(ref=@Model(type=ExternalLink::class))
     */
    private $externalLinks = [];

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
    public function getExternalLinks(): array
    {
        return $this->externalLinks ?? [];
    }

    /**
     * @param ExternalLink[] $externalLinks
     */
    public function setExternalLinks(array $externalLinks): void
    {
        $this->externalLinks = $externalLinks;
    }

}