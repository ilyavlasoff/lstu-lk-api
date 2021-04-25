<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\Mapping\Person;
use App\Model\Request\PersonProperties;
use App\Service\StringConverter;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonalRepository
{
    private $entityManager;
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        $this->entityManager = $entityManager;
        $this->stringConverter = $stringConverter;
    }

    /**
     * @param string $personId
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function isPersonExists(string $personId): bool {
        $prs = $this->entityManager->getConnection()->createQueryBuilder()
            ->select('NP.OID')
            ->from('NPERSONS', 'NP')
            ->where('NP.OID = :PERSON')
            ->setParameter('PERSON', $personId)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);

        return count($prs) === 1;
    }

    /**
     * @param string $nPersonId
     * @return \App\Model\Mapping\Person
     * @throws \Doctrine\DBAL\Exception
     */
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
            throw new NotFoundException('Person');
        }

        $personDataArray = $personalPropertiesDataList[0];
        $personalProps = new Person();
        $personalProps->setUoid($personDataArray['UOID']);
        $personalProps->setEmail($personDataArray['EMAIL']);
        $personalProps->setPatronymic($this->stringConverter->capitalize($personDataArray['PATRONYMIC']));
        $personalProps->setFname($this->stringConverter->capitalize($personDataArray['FNAME']));
        $personalProps->setLname($this->stringConverter->capitalize($personDataArray['LNAME']));
        $personalProps->setBday(new \DateTime($personDataArray['BDAY']));
        $personalProps->setMessenger($personDataArray['MSNGR']);
        $personalProps->setSex($personDataArray['SEX']);
        $personalProps->setPhone($personDataArray['PHONE']);
        $personalProps->setPost($personDataArray['POST']);

        return $personalProps;
    }

    /**
     * @param string $contingentId
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
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
            throw new NotFoundException('Group');
        }

        return $groupsList[0]['GRP'];
    }

    /**
     * @param \App\Model\Request\PersonProperties $newPerson
     * @param string $userOid
     * @throws \Doctrine\DBAL\Exception
     */
    public function updatePerson(PersonProperties $newPerson, string $userOid) {
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
            ->setParameter('PERSONID', $userOid);

        $queryBuilder->execute();
    }

    public function getProfileImage(string $personId) {
        // TODO: implement
        return "";
    }
}