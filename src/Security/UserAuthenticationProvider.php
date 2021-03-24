<?php

namespace App\Security;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserAuthenticationProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    private $documentManager;

    public function __construct(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function supportsClass(string $class)
    {
        return $class === User::class;
    }

    public function refreshUser(UserInterface $user)
    {
        if (! $user instanceof User) {
            throw new UnsupportedUserException(sprintf('User instance type of %s is wrong', get_class($user)));
        }

        $dbUser = $this->documentManager->getRepository(User::class)->find($user->getId());

        if (! $dbUser || ! $dbUser instanceof User)
        {
            throw new UsernameNotFoundException(sprintf('User with email "%s" can not be found',
                $user->getEmail()));
        }

        return $dbUser;
    }

    public function loadUserByUsername(string $username)
    {
        $user = $this->documentManager->getRepository(User::class)->findOneBy(['email' => $username]);

        if (! $user) {
            throw new UsernameNotFoundException(sprintf('User with email "%s" can not be found', $username));
        }

        return $user;
    }

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        return;
    }

}