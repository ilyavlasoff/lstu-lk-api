<?php

namespace App\Model\QueryParam;

use Symfony\Component\Validator\Constraints as Assert;

class TeachingMaterial
{
    /**
     * @var string | null
     * @Assert\NotNull(message="Material identifier was not founded")
     * @Assert\NotBlank(message="Material identifier can not be blank")
     */
    private $material;

    /**
     * @return string|null
     */
    public function getMaterial(): ?string
    {
        return $this->material;
    }

    /**
     * @param string|null $material
     */
    public function setMaterial(?string $material): void
    {
        $this->material = $material;
    }

}