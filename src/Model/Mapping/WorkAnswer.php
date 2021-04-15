<?php

namespace App\Model\Mapping;

class WorkAnswer
{
    /**
     * @var float
     */
    private $score;

    /**
     * @var WorkAttachment[]
     */
    private $attachments;

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
     * @return WorkAttachment[]
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * @param WorkAttachment[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    /**
     * @param WorkAttachment $attachment
     */
    public function addAttachment(WorkAttachment $attachment): void
    {
        $this->attachments[] = $attachment;
    }
}