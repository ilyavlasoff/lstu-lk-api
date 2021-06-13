<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\Constraints as Assert;
use App\Model\DTO\ExternalLink;
use App\Model\DTO\Attachment;
use OpenApi\Annotations as OA;

class WorkAnswerAttachment
{
    /**
     * @var string | null
     * @JMS\ReadOnly()
     * @OA\Property(type="string", description="Идентификатор прикрепленного документа", nullable=false, example="5:32432432")
     */
    private $id;

    /**
     * @var string | null
     * @JMS\Type("string")
     * @Assert\NotNull(message="Attachment name was not found")
     * @Assert\NotBlank(message="Attachment name can not be blank")
     * @OA\Property(type="string", description="Наименование вложения", nullable=false, example="Ответ на задание по методам оптимизации")
     */
    private $name;

    /**
     * @var Attachment[] | null
     * @JMS\Type("array<App\Model\DTO\Attachment>")
     * @OA\Property(ref=@Model(type=Attachment::class), description="Список прикрепленных медиа файлов", nullable=true)
     */
    private $attachments;

    /**
     * @var ExternalLink[] | null
     * @JMS\Type("array<App\Model\DTO\ExternalLink>")
     * @OA\Property(ref=@Model(type=ExternalLink::class), description="Список прикрепленных внешних ссылок", nullable=true)
     */
    private $extLinks;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Attachment[]|null
     */
    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    /**
     * @param Attachment[]|null $attachments
     */
    public function setAttachments(?array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * @return ExternalLink[]|null
     */
    public function getExtLinks(): ?array
    {
        return $this->extLinks;
    }

    /**
     * @param ExternalLink[]|null $extLinks
     */
    public function setExtLinks(?array $extLinks): void
    {
        $this->extLinks = $extLinks;
    }

}