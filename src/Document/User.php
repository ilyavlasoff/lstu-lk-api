<?php

namespace App\Document;

use App\Model\Request\RegisterCredentials;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class User
 * @package App\Document
 * @ODM\Document(collection="usr")
 * @UniqueEntity(
 *     fields={"email"},
 *     message="User with email {{ value }} is already exists"
 * )
 */
class User implements UserInterface
{
    /**
     * @ODM\Id(strategy="INCREMENT")
     */
    private $id;

    /**
     * @ODM\Field(type="string", nullable=false)
     */
    private $email;

    /**
     * @ODM\Field(type="string", nullable=false)
     */
    private $password;

    /**
     * @ODM\Field(type="string", nullable=false)
     */
    private $dbOid;

    /**
     * @ODM\Field(type="collection")
     */
    private $roles;

    /**
     * @inheritDoc
     */
    public function getRoles()
    {
        return array_unique($this->roles);
    }

    /**
     * @inheritDoc
     */
    public function getPassword()
    {
        return (string)$this->password;
    }

    /**
     * @inheritDoc
     */
    public function getSalt()
    {
    }

    /**
     * @inheritDoc
     */
    public function getUsername()
    {
        return (string)$this->getUsername();
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials()
    {
        $this->password = null;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @param mixed $dbOid
     */
    public function setDbOid($dbOid): void
    {
        $this->dbOid = $dbOid;
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

}