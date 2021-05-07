<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class TeachingMaterial
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string | null
     */
    private $materialName;

    /**
     * @var string | null
     */
    private $materialType;

    /**
     * @var Attachment | null
     * @JMS\Type("App\Model\Mapping\Attachment")
     */
    private $attachment;

    /**
     * @var ExternalLink | null
     * @JMS\Type("App\Model\Mapping\ExternalLink")
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