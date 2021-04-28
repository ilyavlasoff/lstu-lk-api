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
            ->where('NP.OID = :PERSONID')
            ->setParameter('PERSONID', $userOid);

        if($updatedEmail = $newPerson->getEmail()) {
            $queryBuilder
                ->set('NP.EMAIL', ':EMAIL')
                ->setParameter('EMAIL', $updatedEmail);
        };

        if($updatedPhone = $newPerson->getPhone()) {
            $queryBuilder
                ->set('NP.TELEPHONS', ':PH')
                ->setParameter('PH', $updatedPhone);
        };

        if($updatedMessenger = $newPerson->getMessenger()) {
            $queryBuilder
                ->set('NP.MASSAGER', ':MSN')
                ->setParameter('MSN', $updatedMessenger);
        };

        $queryBuilder->execute();
    }

    public function getProfileImage(string $personId) {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('NP.PHOTO AS PH')
            ->from('NPERSONS', 'NP')
            ->where('NP.OID = :PERSONID')
            ->setParameter('PERSONID', $personId)
            ->execute();

        $photoData = $result->fetchAll();

        if(count($photoData) !== 1) {
            throw new NotFoundException('Photo');
        }

        return $photoData[0]['PH'];
    }

    /**
     * @param string $personId
     * @param string|null $query
     * @param int $offset
     * @param int $limit
     * @return Person[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function getProfileUsers(string $personId, ?string $query, ?int $offset, ?int $limit): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('NP.OID AS ID, NP.FNAME, NP.FAMILY AS LNAME, NP.MNAME AS PTR, TP.NAME AS POST')
            ->from('NPERSONS', 'NP')
            ->leftJoin('NP','T_POSITIONS', 'TP', 'NP.POSITION = TP.OID');

        if($query) {
            $queryBuilder
                ->where('LOWER(NP.NAME) LIKE LOWER(:QUERY)')
                ->setParameter('QUERY', "%$query%");
        }

        if($limit !== null) {
            $queryBuilder
                ->setMaxResults($limit);
        }

        if($offset !== null) {
            $queryBuilder
                ->setFirstResult($offset);
        }
        $result = $queryBuilder->execute();

        $foundedPersons = [];
        while($personData = $result->fetch()) {
            $person = new Person();
            $person->setUoid($personData['ID']);
            $person->setFname($this->stringConverter->capitalize($personData['FNAME']));
            $person->setLname($this->stringConverter->capitalize($personData['LNAME']));
            $person->setPatronymic($this->stringConverter->capitalize($personData['PTR']));
            $person->setPost($personData['POST']);
            $foundedPersons[] = $person;
        }

        return $foundedPersons;
    }

    /**
     * @param string|null $query
     * @return mixed
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCountOfPersons(?string $query) {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder
            ->select('COUNT(*) CNT')
            ->from('NPERSONS', 'NP');

        if($query) {
            $queryBuilder
                ->where('LOWER(NP.NAME) LIKE LOWER(:QUERY)')
                ->setParameter('QUERY', "%$query%");
        }

        $result = $queryBuilder
            ->execute()
            ->fetchAll();

        return $result[0]['CNT'];
    }
}