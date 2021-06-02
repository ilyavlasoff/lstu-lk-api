<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\Grouping\Day;
use App\Model\DTO\Discipline;
use App\Model\DTO\Education;
use App\Model\DTO\Exam;
use App\Model\DTO\Group;
use App\Model\DTO\Semester;
use App\Model\DTO\Speciality;
use App\Model\DTO\Teacher;
use App\Model\DTO\TimetableItem;
use App\Service\StringConverter;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\FetchMode;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

class EducationRepository extends AbstractRepository
{
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, DocumentManager $documentManager, StringConverter $converter)
    {
        parent::__construct($entityManager, $documentManager);
        $this->stringConverter = $converter;
    }

    /**
     * @param string $education
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function isEducationExists(string $education): bool {
        $edu = $this->getConnection()->createQueryBuilder()
            ->select('EC.OID')
            ->from('ET_CONTINGENTS', 'EC')
            ->where('EC.OID = :EDUCATION')
            ->setParameter('EDUCATION', $education)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);

        return count($edu) === 1;
    }

    /**
     * @param string $semester
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     */
    public function isSemesterExists(string $semester): bool {
        $sem = $this->getConnection()->createQueryBuilder()
            ->select('ES.OID')
            ->from('ET_CSEMESTERS', 'ES')
            ->where('ES.OID = :SEMESTER')
            ->setParameter('SEMESTER', $semester)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);

        return count($sem) === 1;
    }

    /**
     * @param string $group
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function isGroupExists(string $group): bool {
        $groups = $this->getConnection()->createQueryBuilder()
            ->select('G.OID')
            ->from('ET_GROUPS', 'G')
            ->where('G.OID = :GROUP')
            ->setParameter('GROUP', $group)
            ->execute()
            ->fetchFirstColumn();

        return count($groups) === 1;
    }

    /**
     * @param string $personOid
     * @return array
     * @throws \Doctrine\DBAL\Exception
     * @throws \Exception
     * @throws Exception
     */
    public function getLstuEducationListByPerson(string $personOid): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('EC.OID AS EDU_ID, TC.VALUE AS EDU_STATUS, EG.CREATED AS EDU_START, 
            EG.RELEASE_DATE AS EDU_END, COALESCE(EM.VALUE, EM.VALUE, EG.NAME) AS EDU_NAME, 
            TE.VALUE AS EDU_FORM, TQ.VALUE AS EDU_QLFC')
            ->from('NPERSONS', 'NP')
            ->innerJoin('NP', 'ET_CONTINGENTS', 'EC', 'NP.OID = EC.C_OID')
            ->leftJoin('EC', 'T_CONTSTATES', 'TC', 'EC.ESTATE = TC.OID')
            ->innerJoin('EC', 'ET_GROUPS', 'EG', 'EC.G = EG.OID')
            ->leftJoin('EG', 'ET_MAINSPECS', 'EM', 'EG.LEGACY_SPECIALITY = EM.OID')
            ->leftJoin('EM', 'T_EFORMS', 'TE', 'EM.LEGACY_EFORM = TE.OID')
            ->leftJoin('EM', 'T_QUALIFICATION', 'TQ', 'EM.QUALIFICATION = TQ.OID')
            ->where('NP.OID = :STUDENTOID')
            ->setParameter('STUDENTOID', $personOid)
            ->execute();

        $educationList = [];
        while($education = $result->fetchAssociative()) {
            $educationItem = new Education();
            $educationItem->setId($education['EDU_ID']);
            $educationItem->setStatus($education['EDU_STATUS']);

            $educateGroup = new Group();
            $educateGroup->setAdmission($education['EDU_START'] ? new \DateTime($education['EDU_START']) : null);
            $educateGroup->setGraduation($education['EDU_END'] ? new \DateTime($education['EDU_END']) : null);

            $speciality = new Speciality();
            $speciality->setSpecName($education['EDU_NAME']);
            $speciality->setForm($education['EDU_FORM']);
            $speciality->setQualification($education['EDU_QLFC']);

            $educateGroup->setSpeciality($speciality);
            $educationItem->setGroup($educateGroup);

            $educationList[] = $educationItem;
        }

        return $educationList;
    }

    /**
     * @param string $groupId
     * @return Semester
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getCurrentSemester(string $groupId): Semester
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('EC.OID AS SEMID')
            ->from('ET_GROUPS', 'EG')
            ->innerJoin('EG', 'ET_RCONTINGENTS', 'ER', 'EG.OID = ER.G')
            ->innerJoin('ER', 'ET_CSEMESTERS', 'EC', 'ER.CSEMESTER = EC.OID')
            ->innerJoin('EC', 'T_SEMKINDS', 'TS', 'EC.SEMKIND = TS.OID')
            ->where('EG.OID = :GRP')
            ->andWhere('TS.VALUE = CASE WHEN EXTRACT(MONTH FROM CURRENT_DATE) BETWEEN 2 AND 8 THEN \'Весна\' ELSE \'Осень\' END')
            ->andWhere('SUBSTR(EC.NAME, 0, 4) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->setParameter('GRP', $groupId)
            ->execute();

        $semesters = $result->fetchAllAssociative();
        if(count($semesters) !== 1) {
            throw new NotFoundException('Semester');
        }

        $currentSemester = new Semester();
        $currentSemester->setId($semesters[0]['SEMID']);

        return $currentSemester;
    }

    /**
     * @param string $groupId
     * @return array
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getSemesterList(string $groupId): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('EC.OID AS SEM_OID',
            'CASE WHEN TS.OID = \'1:4312\' THEN CAST(TO_NUMBER(TY.NAME) + 1 AS VARCHAR(20)) ELSE TY.NAME END AS YEAR',
            'TS.VALUE AS SEASON')
            ->from('ET_GROUPS', 'EG')
            ->innerJoin('EG', 'ET_RCONTINGENTS','ER', 'EG.OID = ER.G ')
            ->innerJoin('ER', 'ET_CSEMESTERS','EC', 'ER.CSEMESTER = EC.OID')
            ->leftJoin('EC', 'T_YEARS', 'TY', 'EC.YEAR = TY.OID')
            ->leftJoin('EC', 'T_SEMKINDS','TS', 'EC.SEMKIND = TS.OID')
            ->where('EG.OID = :GROUPID')
            ->setParameter('GROUPID', $groupId)
            ->execute();

        $semesterList = [];
        while($semester = $result->fetchAssociative()) {
            $semesterItem = new Semester();
            $semesterItem->setId($semester['SEM_OID']);
            $semesterItem->setYear($semester['YEAR']);
            $semesterItem->setSeason($semester['SEASON']);
            $semesterList[] = $semesterItem;
        }

        return $semesterList;
    }

    /**
     * @param string $person
     * @return array
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getUserEducationsIdList(string $person): array {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('EC.OID AS ID')
            ->from('NPERSONS', 'NP')
            ->innerJoin('NP', 'ET_CONTINGENTS', 'EC', 'NP.OID = EC.C_OID')
            ->where('NP.OID = :PERSON')
            ->setParameter('PERSON', $person)
            ->execute();

        return $result->fetchFirstColumn();
    }

    /**
     * @param string $person
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getUserGroupsIdList(string $person): array {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('EC.G')
            ->from('NPERSONS', 'NP')
            ->innerJoin('NP', 'ET_CONTINGENTS', 'EC', 'NP.OID = EC.C_OID')
            ->where('NP.OID = :PERSON')
            ->setParameter('PERSON', $person)
            ->execute();

        $data = $result->fetchFirstColumn();

        $groups = [];
        foreach ($data as $groupId) {
            $group = new Group();
            $group->setId($groupId);
            $groups[] = $group;
        }

        return $groups;
    }

}