<?php

namespace App\Model\Response;

use JMS\Serializer\Annotation as JMS;

class AuthenticationDataObject
{
    /**
     * @var string
     * @JMS\Type("string")
     */
    private $jwtToken;

    /**
     * @var string
     * @JMS\Type("string")
     */
    private $refreshToken;

    /**
     * @var array
     * @JMS\Type("array")
     */
    private $roles;

    /**
     * @return string
     */
    public function getJwtToken(): string
    {
        return $this->jwtToken;
    }

    /**
     * @param string $jwtToken
     */
    public function setJwtToken(string $jwtToken): void
    {
        $this->jwtToken = $jwtToken;
    }

    /**
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     */
    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

}