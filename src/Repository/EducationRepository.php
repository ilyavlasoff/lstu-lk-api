<?php

namespace App\Repository;

use App\Model\Mapping\Education;
use App\Model\Mapping\Semester;
use Doctrine\ORM\EntityManagerInterface;

class EducationRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getLstuEducationListByPerson(string $personOid): array
    {
        $sql = 'SELECT EC.OID AS EDU_ID, TC.VALUE AS EDU_STATUS, EG.CREATED AS EDU_START, EG.RELEASE_DATE AS EDU_END, ' .
                'COALESCE(EM.VALUE, EM.VALUE, EG.NAME) AS EDU_NAME, TE.VALUE AS EDU_FORM, TQ.VALUE AS EDU_QLFC ' .
                'FROM NPERSONS NP INNER JOIN ET_CONTINGENTS EC on NP.OID = EC.C_OID ' .
                'LEFT JOIN T_CONTSTATES TC ON EC.ESTATE = TC.OID ' .
                'INNER JOIN ET_GROUPS EG ON EC.G = EG.OID ' .
                'LEFT JOIN ET_MAINSPECS EM ON EG.LEGACY_SPECIALITY = EM.OID ' .
                'LEFT JOIN T_EFORMS TE on EM.LEGACY_EFORM = TE.OID ' .
                'LEFT JOIN T_QUALIFICATION TQ ON EM.QUALIFICATION = TQ.OID ' .
                'WHERE NP.OID = :STUDENTOID';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('STUDENTOID', $personOid);
        $query->execute();

        $educationList = [];
        while($education = $query->fetch()) {
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

    public function getCurrentEducations(string $personOid): array
    {

    }

    public function getSemesterList(string $groupId): array
    {
        // В случае, если внесено сочетание года + весеннего семестра, значение года увеличивается на 1 до соответствующего
        $sql = 'SELECT EG.OID AS SEM_OID, CASE WHEN TS.OID = \'1:4312\' THEN CAST(TO_NUMBER(TY.NAME) + 1 AS VARCHAR(20)) ' .
                'ELSE TY.NAME END AS YEAR, TS.VALUE AS SEASON ' .
                'FROM ET_GROUPS EG ' .
                'INNER JOIN ET_RCONTINGENTS ER on EG.OID = ER.G ' .
                'INNER JOIN ET_CSEMESTERS EC on ER.CSEMESTER = EC.OID ' .
                'LEFT JOIN T_YEARS TY on EC.YEAR = TY.OID ' .
                'LEFT JOIN T_SEMKINDS TS ON EC.SEMKIND = TS.OID ' .
                'WHERE EG.OID = :GROUPID';

        $statement = $this->entityManager->getConnection()->prepare($sql);
        $statement->bindValue('GROUPID', $groupId);
        $statement->execute();

        $semesterList = [];
        while($semester = $statement->fetch()) {
            $semesterItem = new Semester();
            $semesterItem->setOid($semester['SEM_OID']);
            $semesterItem->setYear($semester['YEAR']);
            $semesterItem->setSeason($semester['SEASON']);
            $semesterList[] = $semesterItem;
        }

        return $semesterList;
    }
}