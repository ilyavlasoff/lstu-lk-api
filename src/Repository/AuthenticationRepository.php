<?php

namespace App\Repository;

use App\Exception\InheritedSystemException;
use App\Exception\ValueNotFoundException;
use App\Model\Request\UserIdentifier;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\FetchMode;

class AuthenticationRepository extends AbstractRepository
{
    /**
     * @param UserIdentifier $identifier
     * @return mixed
     * @throws InheritedSystemException
     * @throws ValueNotFoundException
     */
    public function identifyUser(UserIdentifier $identifier)
    {
        /*
         * Выборка oid пользователя по предоставленным параметрам UNAME - ФИО, UCODE - номер зачетной книжки, UENTER - год поступления
         */
        $sql = "SELECT p.OID FROM NPERSONS p JOIN ET_CONTINGENTS c ON p.OID = c.C_OID JOIN M_CONTSTATES st ON c.ESTATE = st.OID " .
            "JOIN M_GROUPS mg ON c.G = mg.OID JOIN ET_MAINSPECS esp ON mg.LEGACY_SPECIALITY = esp.OID JOIN M_QUALIFICATION mq ON esp.QUALIFICATION = mq.OID " .
            "WHERE UPPER(mq.name) IN ('Б','С','М','Т') AND st.code IN (1,4) AND LOWER(p.name) = :UNAME AND c.CODE = :UCODE AND EXTRACT(YEAR FROM mg.CREATED) = :UENTER";

        try {
            $query = $this->getConnection()->prepare($sql);
            $query->bindValue('UNAME', mb_strtolower($identifier->getUsername()));
            $query->bindValue('UCODE', $identifier->getZBookNumber());
            $query->bindValue('UENTER', $identifier->getEnteredYear());
            $query->execute();
        } catch (DBALException $e) {
            throw new InheritedSystemException($e, "Statement execute occurred in " . self::class);
        }

        $foundedUsers = $query->fetchAll(FetchMode::ASSOCIATIVE);

        if (count($foundedUsers) !== 1) {
            throw new ValueNotFoundException($identifier->getUsername());
        }
        return $foundedUsers[0]['OID'];
    }
}