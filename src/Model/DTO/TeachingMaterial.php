<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class TeachingMaterial
{
    /**
     * @var string
     * @OA\Property(type="string", description="Идентификатор материала", nullable=false, example="5:495783945")
     */
    private $id;

    /**
     * @var string | null
     * @OA\Property(type="string", description="Наименование материала", nullable=false, example="Методические указания по лабораторным работам")
     */
    private $materialName;

    /**
     * @var string | null
     * @OA\Property(type="string", description="Тип учебного материала", nullable=true, example="Рабочие программы")
     */
    private $materialType;

    /**
     * @var Attachment | null
     * @JMS\Type("App\Model\DTO\Attachment")
     * @OA\Property(ref=@Model(type=Attachment::class), description="Список закрепленных файлов", nullable=true)
     */
    private $attachment;

    /**
     * @var ExternalLink | null
     * @JMS\Type("App\Model\DTO\ExternalLink")
     * @OA\Property(ref=@Model(type=ExternalLink::class), description="Список внешних ссылок", nullable=true)
     */
    private $externalLink;

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
     * @return string|null
     */
    public function getMaterialName(): ?string
    {
        return $this->materialName;
    }

    /**
     * @param string|null $materialName
     */
    public function setMaterialName(?string $materialName): void
    {
        $this->materialName = $materialName;
    }

    /**
     * @return string|null
     */
    public function getMaterialType(): ?string
    {
        return $this->materialType;
    }

    /**
     * @param string|null $materialType
     */
    public function setMaterialType(?string $materialType): void
    {
        $this->materialType = $materialType;
    }

    /**
     * @return Attachment|null
     */
    public function getAttachment(): ?Attachment
    {
        return $this->attachment;
    }

    /**
     * @param Attachment|null $attachment
     */
    public function setAttachment(?Attachment $attachment): void
    {
        $this->attachment = $attachment;
    }

    /**
     * @return ExternalLink|null
     */
    public function getExternalLink(): ?ExternalLink
    {
        return $this->externalLink;
    }

    /**
     * @param ExternalLink|null $externalLink
     */
    public function setExternalLink(?ExternalLink $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

}