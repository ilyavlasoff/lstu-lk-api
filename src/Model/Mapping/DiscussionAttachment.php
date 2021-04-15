<?php

namespace App\Model\Mapping;

class DiscussionAttachment
{
    /**
     * @var string
     */
    private $attachmentId;

    /**
     * @var string | null
     */
    private $fileName;

    /**
     * @var int
     */
    private $fileSize;

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param string|null $fileName
     */
    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @return int
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * @param int $fileSize
     */
    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return string
     */
    public function getAttachmentId(): string
    {
        return $this->attachmentId;
    }

    /**
     * @param string $attachmentId
     */
    public function setAttachmentId(string $attachmentId): void
    {
        $this->attachmentId = $attachmentId;
    }

}