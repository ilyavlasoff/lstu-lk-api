<?php

namespace App\Repository;

use App\Model\Mapping\Person;
use Doctrine\ORM\EntityManagerInterface;

class PersonalRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getPersonalProperties(string $nPersonId): Person
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select('NP.OID UOID, NP.FAMILY AS LNAME,NP.FNAME, NP.MNAME AS PATRONYMIC, 
            NP.CREATED AS BDAY, TS.VALUE AS SEX, NP.TELEPHONS AS PHONE, NP.EMAIL, NP.MASSAGER AS MSNGR, TP.NAME AS POST')
            ->from('NPERSONS', 'NP')
            ->leftJoin('NP', 'T_SEX', 'TS', 'NP.SEX = TS.OID')
            ->leftJoin('NP', 'T_POSITIONS', 'TP', 'NP.POSITION = TP.OID')
            ->where('NP.OID = :OID')
            ->setParameter('OID', $nPersonId)
            ->execute();

        $personalPropertiesDataList = $result->fetchAll();
        if (count($personalPropertiesDataList) !== 1) {
            throw new \Exception('Person not found');
        }

        $personDataArray = $personalPropertiesDataList[0];
        $personalProps = new Person();
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
        $result = $this->entityManager->getConnection()->createQueryBuilder()
            ->select('EC.G AS GRP')
            ->from('ET_CONTINGENTS', 'EC')
            ->where('EC.OID = :CONTINGENT_OID')
            ->setParameter('CONTINGENT_OID', $contingentId)
            ->execute();

        $groupsList = $result->fetchAll();
        if (count($groupsList) !== 1) {
            throw new \Exception('Invalid response');
        }

        return $groupsList[0]['GRP'];
    }

    public function updatePerson(Person $newPerson) {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->update('NPERSONS', 'NP')
            ->set('NP.MASSAGER', ':MSN')
            ->set('NP.TELEPHONS', ':PH')
            ->set('NP.EMAIL', ':EMAIL')
            ->where('NP.OID = :PERSONID')
            ->setParameter('PH', $newPerson->getPhone())
            ->setParameter('EMAIL', $newPerson->getEmail())
            ->setParameter('MSN', $newPerson->getMessenger())
            ->setParameter('PERSONID', $newPerson->getUoid());
        $queryBuilder->execute();
    }
}