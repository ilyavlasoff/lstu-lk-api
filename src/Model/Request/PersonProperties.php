<?php

namespace App\Model\Request;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

class PersonProperties
{
    /**
     * @var string | null
     * @Assert\Length(
     *     min=4,
     *     max=50,
     *     minMessage="Phone value can not be shorted than {{ limit }} symbols",
     *     maxMessage="Phone value can not be longer than {{ limit }} symbols"
     * )
     * @JMS\Type("string")
     */
    private $phone;

    /**
     * @var string | null
     * @Assert\Email(message="Email value is invalid")
     * @JMS\Type("string")
     */
    private $email;

    /**
     * @var string | null
     * @Assert\Length(max=50, maxMessage="Messenger value can not be longer than {{ limit }} symbols")
     * @JMS\Type("string")
     */
    private $messenger;

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

}