<?php

namespace App\Repository;

use App\Document\User;
use App\Exception\DuplicateValueException;
use App\Exception\NotFoundException;
use App\Model\QueryParam\RegisterCredentials;
use App\Model\QueryParam\UserIdentifier;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\FetchMode;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthenticationRepository extends AbstractRepository
{
    private $userPasswordEncoder;

    public function __construct(EntityManagerInterface $entityManager, DocumentManager $documentManager, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        parent::__construct($entityManager, $documentManager);
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @param UserIdentifier $identifier
     * @return mixed
     * @throws Exception
     */
    public function identifyUser(UserIdentifier $identifier)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('P.OID')
            ->from('NPERSONS', 'P')
            ->innerJoin('P', 'ET_CONTINGENTS', 'C', 'P.OID = C.C_OID')
            ->innerJoin('C', 'M_CONTSTATES', 'ST', 'C.ESTATE = ST.OID')
            ->innerJoin('C', 'ET_GROUPS', 'MG', 'C.G = MG.OID')
            ->innerJoin('MG', 'ET_MAINSPECS', 'ESP', 'MG.LEGACY_SPECIALITY = ESP.OID')
            ->innerJoin('ESP', 'T_QUALIFICATION', 'MQ', 'ESP.QUALIFICATION = MQ.OID')
            ->where("UPPER(mq.name) IN ('Б','С','М','Т')")
            ->andWhere('ST.CODE IN (1, 4)')
            ->andWhere('LOWER(P.NAME) = :UNAME')
            ->andWhere('C.CODE = :UCODE')
            ->andWhere('EXTRACT(YEAR FROM MG.CREATED) = :UENTER')
            ->setParameter('UNAME', mb_strtolower($identifier->getUsername()))
            ->setParameter('UCODE', $identifier->getZBookNumber())
            ->setParameter('UENTER', $identifier->getEnteredYear())
            ->execute();

        $foundedUsers = $result->fetchAll(FetchMode::ASSOCIATIVE);

        if (count($foundedUsers) !== 1) {
            throw new NotFoundException('Person');
        }
        return $foundedUsers[0]['OID'];
    }

    /**
     * @param string $oid
     * @return User
     * @throws MongoDBException
     */
    public function persistIdentifiedUser(string $oid): User
    {
        // При получении дублирующегося OID валидация не проходит по unique entity dbOid
        $existingSameOid = $this->getDocumentManager()->getRepository(User::class)->findOneBy(['dbOid' => $oid]);
        if($existingSameOid) {
            throw new DuplicateValueException("Person");
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->getDocumentManager()->getRepository(User::class);

        $user = new User();
        $user->setRoles(['ROLE_IDENTIFIED']);
        $user->setDbOid($oid);
        $user->setEmail(uniqid('email', true));
        $tempPassword = $this->userPasswordEncoder->encodePassword($user, uniqid('password', true));
        $userRepository->upgradePassword($user, $tempPassword);
        $this->getDocumentManager()->persist($user);

        $this->getDocumentManager()->flush();
        return $user;
    }

    /**
     * @param User $currentUser
     * @param RegisterCredentials $registerCredentials
     * @return User
     * @throws MongoDBException
     */
    public function persistRegistration(
        User $currentUser,
        RegisterCredentials $registerCredentials
    ): User {

        $existingSameEmail = $this->getDocumentManager()->getRepository(User::class)
            ->findOneBy(['email' => $registerCredentials->getUsername()]);

        if($existingSameEmail) {
            throw new DuplicateValueException('User');
        }

        /** @var UserRepository $userRepository */
        $userRepository = $this->getDocumentManager()->getRepository(User::class);

        $currentUser->setEmail($registerCredentials->getUsername());
        $updatedPassword = $this->userPasswordEncoder->encodePassword($currentUser, $registerCredentials->getPassword());
        $userRepository->upgradePassword($currentUser, $updatedPassword);
        $currentUser->setRoles(['ROLE_STUDENT']);

        $this->getDocumentManager()->flush();

        return $currentUser;
    }
}