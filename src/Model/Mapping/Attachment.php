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
     * @var resource
     */
    private $attachment;

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
     * @return resource
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param resource $attachment
     */
    public function setAttachment($attachment): void
    {
        $this->attachment = $attachment;
    }


}