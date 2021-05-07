<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class Message
{
    /**
     * @var string
     * @Assert\NotNull(message="Message identifier not found")
     * @Assert\NotBlank(message="Message identifier can not be blank")
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