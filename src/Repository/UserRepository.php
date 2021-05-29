<?php

namespace App\Repository;

use App\Document\User;
use App\Exception\DataAccessException;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends DocumentRepository implements PasswordUpgraderInterface
{
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->dm->persist($user);
        try {
            $this->dm->flush();
        } catch (MongoDBException $e) {
            throw new DataAccessException();
        }
    }

}