<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;

class Dialog
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Dialog field was not found")
     * @Assert\NotBlank(message="Dialog field can not be blank")
     */
    private $dialogId;

    /**
     * @return string|null
     */
    public function getDialogId(): ?string
    {
        return $this->dialogId;
    }

    /**
     * @param string|null $dialogId
     */
    public function setDialogId(?string $dialogId): void
    {
        $this->dialogId = $dialogId;
    }

}