<?php

namespace App\Model\Mapping;

use JMS\Serializer\Annotation as JMS;

class PersonalProperties
{
    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $uoid;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $lname;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $fname;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $patronymic;

    /**
     * @var \DateTime|null
     * @JMS\Type("DateTime<'d.m.Y'>")
     */
    private $bday;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $sex;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $phone;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $email;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $messenger;

    /**
     * @var string|null
     * @JMS\Type("string")
     */
    private $post;

    /**
     * @return string|null
     */
    public function getUoid(): ?string
    {
        return $this->uoid;
    }

    /**
     * @param string|null $uoid
     */
    public function setUoid(?string $uoid): void
    {
        $this->uoid = $uoid;
    }

    /**
     * @return string|null
     */
    public function getLname(): ?string
    {
        return $this->lname;
    }

    /**
     * @param string|null $lname
     */
    public function setLname(?string $lname): void
    {
        $this->lname = $lname;
    }

    /**
     * @return string|null
     */
    public function getFname(): ?string
    {
        return $this->fname;
    }

    /**
     * @param string|null $fname
     */
    public function setFname(?string $fname): void
    {
        $this->fname = $fname;
    }

    /**
     * @return string|null
     */
    public function getPatronymic(): ?string
    {
        return $this->patronymic;
    }

    /**
     * @param string|null $patronymic
     */
    public function setPatronymic(?string $patronymic): void
    {
        $this->patronymic = $patronymic;
    }

    /**
     * @return \DateTime|null
     */
    public function getBday(): ?\DateTime
    {
        return $this->bday;
    }

    /**
     * @param \DateTime|null $bday
     */
    public function setBday(?\DateTime $bday): void
    {
        $this->bday = $bday;
    }

    /**
     * @return string|null
     */
    public function getSex(): ?string
    {
        return $this->sex;
    }

    /**
     * @param string|null $sex
     */
    public function setSex(?string $sex): void
    {
        $this->sex = $sex;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getMessenger(): ?string
    {
        return $this->messenger;
    }

    /**
     * @param string|null $messenger
     */
    public function setMessenger(?string $messenger): void
    {
        $this->messenger = $messenger;
    }

    /**
     * @return string|null
     */
    public function getPost(): ?string
    {
        return $this->post;
    }

    /**
     * @param string|null $post
     */
    public function setPost(?string $post): void
    {
        $this->post = $post;
    }

}