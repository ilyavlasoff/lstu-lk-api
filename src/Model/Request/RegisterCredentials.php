<?php

namespace App\Model\Request;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterCredentials
{
    /**
     * @JMS\Type("string")
     * @Assert\NotNull(message="Username field was not found")
     * @Assert\NotBlank(message="Username field can not be blank")
     * @Assert\Email(message="This username is not a valid e-mail")
     */
    private $username;

    /**
     * @JMS\Type("string")
     * @Assert\NotNull(message="Password field was not found")
     * @Assert\NotBlank(message="Password field can not be blank")
     * @Assert\NotCompromisedPassword(message="This password may be leaked")
     * @Assert\Length(min="6", minMessage="Minimal password length is {{ limit }} symbols")
     */
    private $password;

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

}