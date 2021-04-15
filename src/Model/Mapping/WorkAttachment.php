<?php

namespace App\Model\Mapping;

class WorkAttachment
{
    /**
     * @var string
     */
    private $attachmentId;

    // TODO: Add student field
    /*
     * @var string | null
     */
    //private $student;

    /**
     * @var int | null
     */
    private $fileSize;

    /**
     * @var string | null
     */
    private $fileName;

    /**
     * @var string | null
     */
    private $displayedName;

    /**
     * @var string | null
     */
    private $externalLink;

    /**
     * @var int | null
     */
    private $score;

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

    /**
     * @return int|null
     */
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    /**
     * @param int|null $fileSize
     */
    public function setFileSize(?int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

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
     * @return string|null
     */
    public function getDisplayedName(): ?string
    {
        return $this->displayedName;
    }

    /**
     * @param string|null $displayedName
     */
    public function setDisplayedName(?string $displayedName): void
    {
        $this->displayedName = $displayedName;
    }

    /**
     * @return string|null
     */
    public function getExternalLink(): ?string
    {
        return $this->externalLink;
    }

    /**
     * @param string|null $externalLink
     */
    public function setExternalLink(?string $externalLink): void
    {
        $this->externalLink = $externalLink;
    }

    /**
     * @return int|null
     */
    public function getScore(): ?int
    {
        return $this->score;
    }

    /**
     * @param int|null $score
     */
    public function setScore(?int $score): void
    {
        $this->score = $score;
    }

}