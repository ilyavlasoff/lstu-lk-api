<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class Achievement
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string | null
     */
    private $name;

    /**
     * @var \DateTime | null
     */
    private $achievedDate;

    /**
     * @var string | null
     */
    private $kind;

    /**
     * @var string | null
     */
    private $type;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return \DateTime|null
     */
    public function getAchievedDate(): ?\DateTime
    {
        return $this->achievedDate;
    }

    /**
     * @param \DateTime|null $achievedDate
     */
    public function setAchievedDate(?\DateTime $achievedDate): void
    {
        $this->achievedDate = $achievedDate;
    }

    /**
     * @return string|null
     */
    public function getKind(): ?string
    {
        return $this->kind;
    }

    /**
     * @param string|null $kind
     */
    public function setKind(?string $kind): void
    {
        $this->kind = $kind;
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

}