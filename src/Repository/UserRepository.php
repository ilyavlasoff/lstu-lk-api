<?php

namespace App\Repository;

use App\Document\User;
use App\Exception\DuplicateValueException;
use App\Exception\InheritedSystemException;
use App\Exception\ValidationException;
use App\Exception\ResourceNotFoundException;
use App\Model\Request\RegisterCredentials;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRepository
{
    private $documentManager;
    private $passwordEncoder;
    private $validator;

    public function __construct(
        DocumentManager $documentManager,
        UserPasswordEncoderInterface $passwordEncoder,
        ValidatorInterface $validator
    )
    {
        $this->documentManager = $documentManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->validator = $validator;
    }

    /**
     * @param string $oid
     * @return User
     * @throws \App\Exception\DuplicateValueException
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    public function persistIdentifiedUser(string $oid): User
    {
        // При получении дублирующегося OID валидация не проходит по unique entity dbOid
        $existingSameOid = $this->documentManager->getRepository(User::class)->findOneBy(['dbOid' => $oid]);
        if($existingSameOid) {
            throw new DuplicateValueException("Person");
        }

        $user = new User();
        $user->setRoles(['ROLE_IDENTIFIED']);
        $user->setDbOid($oid);
        $user->setEmail(uniqid('email', true));
        $user->setPassword($this->passwordEncoder->encodePassword($user, uniqid('password', true)));
        $this->documentManager->persist($user);

        $this->documentManager->flush();
        return $user;
    }

    /**
     * @param User $currentUser
     * @param RegisterCredentials $registerCredentials
     * @return User
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \App\Exception\DuplicateValueException
     */
    public function persistRegistration(
        User $currentUser,
        RegisterCredentials $registerCredentials
    ): User {

        $existingSameEmail = $this->documentManager->getRepository(User::class)
            ->findOneBy(['email' => $registerCredentials->getUsername()]);
        if($existingSameEmail) {
            throw new DuplicateValueException('User');
        }

        $currentUser->setEmail($registerCredentials->getUsername());
        $currentUser->setPassword($this->passwordEncoder->encodePassword($currentUser, $registerCredentials->getPassword()));
        $currentUser->setRoles(['ROLE_STUDENT']);

        $this->documentManager->flush();

        return $currentUser;
    }
}