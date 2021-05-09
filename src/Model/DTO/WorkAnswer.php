<?php

namespace App\Model\DTO;

class WorkAnswer
{
    /**
     * @var float
     */
    private $score;

    /**
     * @var WorkAnswerAttachment[]
     */
    private $answerAttachments;

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
     * @return WorkAnswerAttachment[]
     */
    public function getAnswerAttachments(): array
    {
        return $this->answerAttachments;
    }

    /**
     * @param WorkAnswerAttachment[] $answerAttachments
     */
    public function setAnswerAttachments(array $answerAttachments): void
    {
        $this->answerAttachments = $answerAttachments;
    }

    /**
     * @param WorkAnswerAttachment $answerAttachments
     */
    public function addAnswerAttachments(WorkAnswerAttachment $answerAttachments): void
    {
        $this->answerAttachments[] = $answerAttachments;
    }

}