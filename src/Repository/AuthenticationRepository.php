<?php

namespace App\Repository;

use App\Exception\InheritedSystemException;
use App\Exception\InvalidCredentialsException;
use App\Exception\NotFoundException;
use App\Exception\ResourceNotFoundException;
use App\Model\Request\UserIdentifier;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;

class AuthenticationRepository extends AbstractRepository
{
    /**
     * @param UserIdentifier $identifier
     * @return mixed
     * @throws \Doctrine\DBAL\Exception
     */
    public function identifyUser(UserIdentifier $identifier)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('P.OID')
            ->from('NPERSONS', 'P')
            ->innerJoin('P', 'ET_CONTINGENTS', 'C', 'P.OID = C.C_OID')
            ->innerJoin('C', 'M_CONTSTATES', 'ST', 'C.ESTATE = ST.OID')
            ->innerJoin('C', 'M_GROUPS', 'MG', 'C.G = MG.OID')
            ->innerJoin('MG', 'ET_MAINSPECS', 'ESP', 'MG.LEGACY_SPECIALITY = ESP.OID')
            ->innerJoin('ESP', 'M_QUALIFICATION', 'MQ', 'ESP.QUALIFICATION = MQ.OID')
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
}