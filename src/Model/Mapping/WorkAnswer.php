<?php

namespace App\Model\Mapping;

class WorkAnswer
{
    /**
     * @var float
     */
    private $score;

    /**
     * @var \App\Model\Mapping\Attachment[]
     */
    private $attachments;

    /**
     * @var \App\Model\Mapping\ExternalLink[]
     */
    private $extLinks;

    /**
     * @return float
     */
    public function getScore(): float
    {
        return $this->score;
    }

    /**
     * @param float $score
     */
    public function setScore(float $score): void
    {
        $this->score = $score;
    }

    /**
     * @return \App\Model\Mapping\Attachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param \App\Model\Mapping\Attachment[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * @param \App\Model\Mapping\Attachment $attachment
     */
    public function addAttachment(Attachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @return \App\Model\Mapping\ExternalLink[]
     */
    public function getExtLinks(): array
    {
        return $this->extLinks;
    }

    /**
     * @param \App\Model\Mapping\ExternalLink[] $extLinks
     */
    public function setExtLinks(array $extLinks): void
    {
        $this->extLinks = $extLinks;
    }

    /**
     * @param \App\Model\Mapping\ExternalLink $extLink
     */
    public function addExtLink(ExternalLink $extLink): void
    {
        $this->extLinks[] = $extLink;
    }

}