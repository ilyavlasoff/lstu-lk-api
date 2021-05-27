<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class WithJsonFlag
{
    /**
     * @var bool | null
     * @Assert\Type(type="bool", message="With json flag must be boolean")
     */
    private $withJsonData;

    /**
     * @return bool|null
     */
    public function getWithJsonData(): ?bool
    {
        return $this->withJsonData;
    }

    /**
     * @param bool|null $withJsonData
     */
    public function setWithJsonData(?bool $withJsonData): void
    {
        $this->withJsonData = $withJsonData;
    }

}