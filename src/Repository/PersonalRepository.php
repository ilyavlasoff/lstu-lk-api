<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\DTO\Person;
use App\Model\QueryParam\PersonProperties;
use App\Service\StringConverter;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PersonalRepository extends AbstractRepository
{
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        parent::__construct($entityManager);
        $this->stringConverter = $stringConverter;
    }

    /**
     * @param string $personId
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function isPersonExists(string $personId): bool {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('COUNT(NP.OID) AS CNT')
            ->from('NPERSONS', 'NP')
            ->where('NP.OID = :PERSON')
            ->setParameter('PERSON', $personId)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
    }

    /**
     * @param string $educationId
     * @param string $personId
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function isEducationBelongsToUser(string $educationId, string $personId): bool
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('COUNT(*) AS CNT')
            ->from('NPERSONS', 'NP')
            ->innerJoin('NP', 'ET_CONTINGENTS', 'ETC', 'ETC.C_OID = NP.OID')
            ->where('ETC.OID = :EDUCATION AND NP.OID = :PERSON')
            ->setParameter('EDUCATION', $educationId)
            ->setParameter('PERSON', $personId)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
    }

    /**
     * @param string $nPersonId
     * @return Person
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Exception
     */
    public function getPersonalProperties(string $nPersonId): Person
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select('NP.OID UOID, NP.FAMILY AS LNAME,NP.FNAME, NP.MNAME AS PATRONYMIC, 
            NP.CREATED AS BDAY, TS.VALUE AS SEX, NP.TELEPHONS AS PHONE, NP.EMAIL, NP.MASSAGER AS MSNGR, TP.NAME AS POST')
            ->from('NPERSONS', 'NP')
            ->leftJoin('NP', 'T_SEX', 'TS', 'NP.SEX = TS.OID')
            ->leftJoin('NP', 'T_POSITIONS', 'TP', 'NP.POSITION = TP.OID')
            ->where('NP.OID = :OID')
            ->setParameter('OID', $nPersonId)
            ->execute();

        $personalPropertiesDataList = $result->fetchAllAssociative();
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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getGroupByContingent(string $contingentId): string
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('EC.G AS GRP')
            ->from('ET_CONTINGENTS', 'EC')
            ->where('EC.OID = :CONTINGENT_OID')
            ->setParameter('CONTINGENT_OID', $contingentId)
            ->execute();

        $groupsList = $result->fetchAllAssociative();
        if (count($groupsList) !== 1) {
            throw new NotFoundException('Group');
        }

        return $groupsList[0]['GRP'];
    }

    /**
     * @param PersonProperties $newPerson
     * @param string $userOid
     * @throws Exception
     */
    public function updatePerson(PersonProperties $newPerson, string $userOid) {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
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
                ->set('NP.TELEPHONE', ':PH')
                ->setParameter('PH', $updatedPhone);
        };

        if($updatedMessenger = $newPerson->getMessenger()) {
            $queryBuilder
                ->set('NP.MASSAGER', ':MSN')
                ->setParameter('MSN', $updatedMessenger);
        };

        $queryBuilder->execute();
    }

    /**
     * @param string $personId
     * @return mixed
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getProfileImage(string $personId) {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('NP.PHOTO AS PH')
            ->from('NPERSONS', 'NP')
            ->where('NP.OID = :PERSONID')
            ->setParameter('PERSONID', $personId)
            ->execute();

        $photoData = $result->fetchAllAssociative();

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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getProfileUsers(string $personId, ?string $query, ?int $offset, ?int $limit): array
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
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
        while($personData = $result->fetchAssociative()) {
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
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getCountOfPersons(?string $query) {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

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
            ->fetchAllAssociative();

        return $result[0]['CNT'];
    }
}