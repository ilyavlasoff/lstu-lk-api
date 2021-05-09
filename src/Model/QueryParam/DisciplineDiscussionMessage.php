<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class DisciplineDiscussionMessage
{
    /**
     * @var string
     * @Assert\NotNull(message="DisciplineDiscussionMessage identifier not found")
     * @Assert\NotBlank(message="DisciplineDiscussionMessage identifier can not be blank")
     */
    private $msg;

    /**
     * @return string
     */
    public function getMsg(): string
    {
        return $this->msg;
    }

    /**
     * @param string $msg
     */
    public function setMsg(string $msg): void
    {
        $this->msg = $msg;
    }

}