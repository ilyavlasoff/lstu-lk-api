<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class TaskAnswer
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Answer identifier was not founded")
     * @Assert\NotBlank(message="Answer identifier can not be blank")
     */
    private $answer;

    /**
     * @return string|null
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * @param string|null $answer
     */
    public function setAnswer(?string $answer): void
    {
        $this->answer = $answer;
    }

}