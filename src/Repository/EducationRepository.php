<?php

namespace App\Repository;

use App\Model\Grouping\Day;
use App\Model\Mapping\Discipline;
use App\Model\Mapping\Education;
use App\Model\Mapping\Exam;
use App\Model\Mapping\Semester;
use App\Model\Mapping\Teacher;
use App\Model\Mapping\TimetableItem;
use App\Service\StringConverter;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;

class EducationRepository
{
    private $entityManager;
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        $this->entityManager = $entityManager;
        $this->stringConverter = $stringConverter;
    }

    public function getLstuEducationListByPerson(string $personOid): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
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
        while($education = $result->fetch()) {
            $educationItem = new Education();
            $educationItem->setId($education['EDU_ID']);
            $educationItem->setStatus($education['EDU_STATUS']);
            $educationItem->setStart($education['EDU_START'] ? new \DateTime($education['EDU_START']) : null);
            $educationItem->setEnd($education['EDU_END'] ? new \DateTime($education['EDU_END']) : null);
            $educationItem->setName($education['EDU_NAME']);
            $educationItem->setForm($education['EDU_FORM']);
            $educationItem->setQualification($education['EDU_QLFC']);
            $educationList[] = $educationItem;
        }

        return $educationList;
    }

    public function getCurrentSemester(string $groupId)
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('EC.OID AS SEMID')
            ->from('ET_GROUPS', 'EG')
            ->innerJoin('EG', 'ET_RCONTINGENTS', 'ER', 'EG.OID = ER.G')
            ->innerJoin('ER', 'ET_CSEMESTERS', 'EC', 'ER.CSEMESTER = EC.OID')
            ->innerJoin('EC', 'T_SEMKINDS', 'TC', 'EC.SEMKIND = TS.OID')
            ->where('EG.OID = :GRP')
            ->andWhere('TS.VALUE = CASE WHEN EXTRACT(MONTH FROM CURRENT_DATE) BETWEEN 2 AND 8 THEN \'Весна\' ELSE \'Осень\' END')
            ->andWhere('SUBSTR(EC.NAME, 0, 4) = EXTRACT(YEAR FROM CURRENT_DATE)')
            ->setParameter('GRP', $groupId)
            ->execute();

        $semesters = $result->fetchAll();
        if(count($semesters) !== 1) {
            throw new \Exception('Semester not found');
        }

        $currentSemester = new Semester();
        $currentSemester->setOid($semesters[0]['SEMID']);

        return $currentSemester;
    }

    public function getSemesterList(string $groupId): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
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
        while($semester = $result->fetch()) {
            $semesterItem = new Semester();
            $semesterItem->setOid($semester['SEM_OID']);
            $semesterItem->setYear($semester['YEAR']);
            $semesterItem->setSeason($semester['SEASON']);
            $semesterList[] = $semesterItem;
        }

        return $semesterList;
    }

}