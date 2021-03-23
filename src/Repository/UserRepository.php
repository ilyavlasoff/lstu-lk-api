<?php

namespace App\Repository;

use App\Document\User;
use App\Exception\InheritedSystemException;
use App\Exception\ValueNotFoundException;
use App\Model\Request\RegisterCredentials;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserRepository
{
    private $documentManager;
    private $passwordEncoder;

    public function __construct(DocumentManager $documentManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->documentManager = $documentManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @param string $oid
     * @return User
     * @throws InheritedSystemException
     */
    public function persistIdentifiedUser(string $oid): User
    {
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