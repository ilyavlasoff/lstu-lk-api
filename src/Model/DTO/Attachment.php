<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class Attachment
{
    /**
     * @var string | null
     * @JMS\Type("string")
     * @OA\Property(type="string", nullable=true, description="Наименование вложения")
     */
    private $attachmentName;

    /**
     * @var float | null
     * @JMS\Type("string")
     * @OA\Property(type="float", nullable=true, description="Размер в байтах")
     */
    private $attachmentSize;

    /**
     * @var string | null
     * @JMS\Type("string")
     * @OA\Property(type="string", nullable=true, description="Документ в base64")
     */
    private $b64attachment;

    /**
     * @return BinaryFile
     */
    public function toBinary(): BinaryFile {
        $binaryFile = new BinaryFile();
        $binaryFile->setFilename($this->attachmentName);
        $binaryFile->setFileContent(bin2hex(base64_decode($this->b64attachment)));
        return $binaryFile;
    }

    /**
     * @return string|null
     */
    public function getAttachmentName(): ?string
    {
        return $this->attachmentName;
    }

    /**
     * @param string|null $attachmentName
     */
    public function setAttachmentName(?string $attachmentName): void
    {
        $this->attachmentName = $attachmentName;
    }

    /**
     * @return float|null
     */
    public function getAttachmentSize(): ?float
    {
        return $this->attachmentSize;
    }

    /**
     * @param float|null $attachmentSize
     */
    public function setAttachmentSize(?float $attachmentSize): void
    {
        $this->attachmentSize = $attachmentSize;
    }

    /**
     * @return string|null
     */
    public function getB64attachment(): ?string
    {
        return $this->b64attachment;
    }

    /**
     * @param string|null $b64attachment
     */
    public function setB64attachment(?string $b64attachment): void
    {
        $this->b64attachment = $b64attachment;
    }


}