<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class PrivateMessage
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Private message parameter was not founded")
     * @Assert\NotBlank(message="Private message parameter can not be blank")
     */
    private $msg;

    /**
     * @return string|null
     */
    public function getMsg(): ?string
    {
        return $this->msg;
    }

    /**
     * @param string|null $msg
     */
    public function setMsg(?string $msg): void
    {
        $this->msg = $msg;
    }

}