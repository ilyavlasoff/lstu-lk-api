<?php

namespace App\Model\DTO;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class WorkAnswer
{
    /**
     * @var float | null
     * @OA\Property(type="float", description="Балл, полученный за данную работу студентом", nullable=true, example="4.0")
     */
    private $score;

    /**
     * @var WorkAnswerAttachment[]
     * @OA\Property(ref=@Model(type=WorkAnswerAttachment::class), description="Список прикрепленных данных", nullable=false)
     */
    private $answerAttachments;

    /**
     * @return float|null
     */
    public function getScore(): ?float
    {
        return $this->score;
    }

    /**
     * @param float|null $score
     */
    public function setScore(?float $score): void
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