<?php

namespace App\Model\Mapping;

class Attachment
{
    /**
     * @var string | null
     */
    private $mimeType;

    /**
     * @var string | null
     */
    private $attachmentName;

    /**
     * @var float | null
     */
    private $attachmentSize;

    /**
     * @var string | null
     */
    private $b64attachment;

    /**
     * @return BinaryFile
     */
    public function toBinary(): BinaryFile {
        $binaryFile = new BinaryFile();
        $binaryFile->setFilename($this->attachmentName);
        $binaryFile->setFileContent(base64_decode($this->b64attachment));
        return $binaryFile;
    }

    /**
     * @return string|null
     */
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    /**
     * @param string|null $mimeType
     */
    public function setMimeType(?string $mimeType): void
    {
        $this->mimeType = $mimeType;
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