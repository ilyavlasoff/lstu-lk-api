<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class Group
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Group identifier is was not found")
     * @Assert\NotBlank(message="Group identifier is empty")
     */
    private $groupId;

    /**
     * @return string|null
     */
    public function getGroupId(): ?string
    {
        return $this->groupId;
    }

    /**
     * @param string|null $groupId
     */
    public function setGroupId(?string $groupId): void
    {
        $this->groupId = $groupId;
    }

}