<?php

namespace App\Model\Mapping;
use JMS\Serializer\Annotation as JMS;

class Education
{
    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $id;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $status;

    /**
     * @var \DateTime|null
     * @JMS\Type("DateTime<'m.Y'>")
     */
    private $start;

    /**
     * @var \DateTime|null
     * @JMS\Type("DateTime<'m.Y'>")
     */
    private $end;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $name;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $form;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $qualification;

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
     * @return \DateTime|null
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime|null $start
     */
    public function setStart(?\DateTime $start): void
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime|null $end
     */
    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getForm(): ?string
    {
        return $this->form;
    }

    /**
     * @param string|null $form
     */
    public function setForm(?string $form): void
    {
        $this->form = $form;
    }

    /**
     * @return string|null
     */
    public function getQualification(): ?string
    {
        return $this->qualification;
    }

    /**
     * @param string|null $qualification
     */
    public function setQualification(?string $qualification): void
    {
        $this->qualification = $qualification;
    }

}