<?php

namespace App\Repository;

use App\Exception\ValueNotFoundException;
use App\Model\Mapping\PersonalProperties;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class PersonalRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getPersonalProperties(string $nPersonId): PersonalProperties
    {
        $sql = "SELECT NP.OID UOID, NP.FAMILY AS LNAME,NP.FNAME, NP.MNAME AS PATRONYMIC, NP.CREATED AS BDAY, TS.VALUE AS SEX, " .
            "NP.TELEPHONS AS PHONE, NP.EMAIL, NP.MASSAGER AS MSNGR, TP.NAME AS POST " .
            "FROM NPERSONS NP LEFT JOIN T_SEX TS on NP.SEX = TS.OID LEFT JOIN T_POSITIONS TP on NP.POSITION = TP.OID " .
            "WHERE NP.OID = :OID";

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('OID', $nPersonId);
        $query->execute();

        $personalPropertiesDataList = $query->fetchAll();
        if (count($personalPropertiesDataList) !== 1) {
            throw new \Exception('Person not found');
        }

        $personDataArray = $personalPropertiesDataList[0];
        $personalProps = new PersonalProperties();
        $personalProps->setUoid($personDataArray['UOID']);
        $personalProps->setEmail($personDataArray['EMAIL']);
        $personalProps->setPatronymic($personDataArray['PATRONYMIC']);
        $personalProps->setFname($personDataArray['FNAME']);
        $personalProps->setLname($personDataArray['LNAME']);
        $personalProps->setBday(new \DateTime($personDataArray['BDAY']));
        $personalProps->setMessenger($personDataArray['MSNGR']);
        $personalProps->setSex($personDataArray['SEX']);
        $personalProps->setPhone($personDataArray['PHONE']);
        $personalProps->setPost($personDataArray['POST']);

        return $personalProps;
    }

    public function getGroupByContingent(string $contingentId): string
    {
        $sql = 'SELECT G AS GRP FROM ET_CONTINGENTS WHERE OID = :CONTINGENT_OID';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('CONTINGENT_OID', $contingentId);
        $query->execute();

        $groupsList = $query->fetchAll();
        if (count($groupsList) !== 1) {
            throw new \Exception('Invalid response');
        }

        return $groupsList[0]['GRP'];
    }
}