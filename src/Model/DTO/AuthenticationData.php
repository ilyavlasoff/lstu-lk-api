<?php

namespace App\Model\DTO;

use JMS\Serializer\Annotation as JMS;
use OpenApi\Annotations as OA;

class AuthenticationData
{
    /**
     * @var string
     * @JMS\SerializedName("token")
     * @OA\Property(type="string", nullable=false, description="Токен аутентификации пользователя")
     */
    private $jwtToken;

    /**
     * @var string
     * @OA\Property(type="string", nullable=false, description="Токен обновления JWT")
     */
    private $refreshToken;

    /**
     * @var array
     * @OA\Property(type="string", nullable=false, description="Роль пользователя", example="ROLE_STUDENT")
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