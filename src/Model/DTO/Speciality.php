<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;

class Speciality
{
    /**
     * @var string | null
     */
    private $id;

    /**
     * @var string | null
     */
    private $specName;

    /**
     * @var string | null
     */
    private $specNameAbbr;

    /**
     * @var string | null
     */
    private $qualification;

    /**
     * @var string | null
     */
    private $form;

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
    public function getSpecName(): ?string
    {
        return $this->specName;
    }

    /**
     * @param string|null $specName
     */
    public function setSpecName(?string $specName): void
    {
        $this->specName = $specName;
    }

    /**
     * @return string|null
     */
    public function getSpecNameAbbr(): ?string
    {
        return $this->specNameAbbr;
    }

    /**
     * @param string|null $specNameAbbr
     */
    public function setSpecNameAbbr(?string $specNameAbbr): void
    {
        $this->specNameAbbr = $specNameAbbr;
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

}