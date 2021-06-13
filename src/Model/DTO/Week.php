<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Week
{
    /**
     * @var bool | null
     * @OA\Property(type="boolean", nullable=true, description="Флаг текущей недели", example="true")
     */
    private $current;

    /**
     * @var string | null
     * @OA\Property(type="string", nullable=true, description="Наименование недели", example="green")
     */
    private $type;

    /**
     * @var Day[]
     * @JMS\Type("array<App\Model\DTO\Day>")
     * @OA\Property(ref=@Model(type=Day::class))
     */
    private $days;

    /**
     * @return bool|null
     */
    public function getCurrent(): ?bool
    {
        return $this->current;
    }

    /**
     * @param bool|null $current
     */
    public function setCurrent(?bool $current): void
    {
        $this->current = $current;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return Day[]
     */
    public function getDays(): array
    {
        return $this->days;
    }

    /**
     * @param Day[] $days
     */
    public function setDays(array $days): void
    {
        $this->days = $days;
    }

}