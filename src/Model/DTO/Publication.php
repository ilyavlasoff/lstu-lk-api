<?php

namespace App\Model\DTO;

class Publication
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string | null
     */
    private $title;

    /**
     * @var string | null
     */
    private $description;

    /**
     * @var string | null
     */
    private $published;

    /**
     * @var string | null
     */
    private $pubType;

    /**
     * @var string | null
     */
    private $pubForm;

    /**
     * @var string | null
     */
    private $pubFormValue;

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
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getPublished(): ?string
    {
        return $this->published;
    }

    /**
     * @param string|null $published
     */
    public function setPublished(?string $published): void
    {
        $this->published = $published;
    }

    /**
     * @return string|null
     */
    public function getPubType(): ?string
    {
        return $this->pubType;
    }

    /**
     * @param string|null $pubType
     */
    public function setPubType(?string $pubType): void
    {
        $this->pubType = $pubType;
    }

    /**
     * @return string|null
     */
    public function getPubForm(): ?string
    {
        return $this->pubForm;
    }

    /**
     * @param string|null $pubForm
     */
    public function setPubForm(?string $pubForm): void
    {
        $this->pubForm = $pubForm;
    }

    /**
     * @return string|null
     */
    public function getPubFormValue(): ?string
    {
        return $this->pubFormValue;
    }

    /**
     * @param string|null $pubFormValue
     */
    public function setPubFormValue(?string $pubFormValue): void
    {
        $this->pubFormValue = $pubFormValue;
    }

}