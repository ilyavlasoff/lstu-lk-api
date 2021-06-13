<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class Education
{
    /**
     * @var string|null
     * @OA\Property(type="string", nullable=true, description="Идентификатор периода обучения", example="5:2498745")
     */
    private $id;

    /**
     * @var string|null
     * @OA\Property(type="string", nullable=true, description="Статус обучения на данный момент", example="Учится")
     */
    private $status;

    /**
     * @var \App\Model\DTO\Group | null
     * @OA\Property(ref=@Model(type=Group::class))
     */
    private $group;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return \App\Model\DTO\Group|null
     */
    public function getGroup(): ?Group
    {
        return $this->group;
    }

    /**
     * @param \App\Model\DTO\Group|null $group
     */
    public function setGroup(?Group $group): void
    {
        $this->group = $group;
    }

}