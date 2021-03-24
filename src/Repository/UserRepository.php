<?php

namespace App\Repository;

use App\Document\User;
use App\Exception\DuplicateValueException;
use App\Exception\InheritedSystemException;
use App\Exception\ValidationException;
use App\Exception\ValueNotFoundException;
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
     * @throws InheritedSystemException
     * @throws ValidationException
     */
    public function persistIdentifiedUser(string $oid): User
    {
        // При получении дублирующегося OID валидация не проходит по unique entity dbOid
        $existingSameOid = $this->documentManager->getRepository(User::class)->findOneBy(['dbOid' => $oid]);
        if($existingSameOid) {
            throw new DuplicateValueException(['oid'], 'This user has already registered');
        }

        $user = new User();
        $user->setRoles(['ROLE_IDENTIFIED']);
        $user->setDbOid($oid);
        $user->setEmail(uniqid('email', true));
        $user->setPassword($this->passwordEncoder->encodePassword($user, uniqid('password', true)));
        $this->documentManager->persist($user);

        try {
            $this->documentManager->flush();
        } catch (MongoDBException $e) {
            throw new InheritedSystemException($e, "Unable to persist identified user");
        }
        return $user;
    }

    /**
     * @param User $currentUser
     * @param RegisterCredentials $registerCredentials
     * @return User
     * @throws InheritedSystemException
     * @throws ValueNotFoundException
     */
    public function persistRegistration(
        User $currentUser,
        RegisterCredentials $registerCredentials
    ): User {
        if (!$currentUser || !in_array('ROLE_IDENTIFIED', $currentUser->getRoles(), true)) {
            throw new ValueNotFoundException(User::class, 'User was not found');
        }

        $existingSameEmail = $this->documentManager->getRepository(User::class)
            ->findOneBy(['email' => $registerCredentials->getUsername()]);
        if($existingSameEmail) {
            throw new DuplicateValueException(['email'], 'This email has already used');
        }

        $currentUser->setEmail($registerCredentials->getUsername());
        $currentUser->setPassword($this->passwordEncoder->encodePassword($currentUser, $registerCredentials->getPassword()));
        $currentUser->setRoles(['ROLE_STUDENT']);

        try {
            $this->documentManager->flush();
        } catch (MongoDBException $e) {
            throw new InheritedSystemException($e, "Unable to persist identified user");
        }

        return $currentUser;
    }
}