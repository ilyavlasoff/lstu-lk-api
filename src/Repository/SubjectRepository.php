<?php

namespace App\Repository;

use App\Model\Mapping\AcademicSubject;
use App\Service\StringConverter;
use Doctrine\ORM\EntityManagerInterface;

class SubjectRepository
{
    private $entityManager;
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        $this->entityManager = $entityManager;
        $this->stringConverter = $stringConverter;
    }

    public function getSubjectsBySemester(string $groupId, string $semesterId): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select('EDIS.OID as DISCIPLINE_ID, EDIS.NAME AS DISCIPLINE_NAME, ECH.VALUE AS CHAIR_NAME')
            ->from('ET_RCONTINGENTS', 'ER')
            ->innerJoin('ER', 'ET_GROUPS', 'EG', 'ER.G = EG.OID')
            ->innerJoin('ER', 'ET_CSEMESTERS', 'ECSEM', 'ER.CSEMESTER = ECSEM.OID')
            ->innerJoin('ER', 'ET_CURRICULUMS', 'EC', 'ER.PLAN = EC.OID')
            ->innerJoin('EC', 'ET_DSPLANS', 'ED', $queryBuilder->expr()->andX('EC.OID = ED.EPLAN', 'ED.SEMESTER = ER.SEMESTER'))
            ->innerJoin('ED', 'ET_DISCIPLINES', 'EDIS', 'ED.DISCIPLINE = EDIS.OID')
            ->leftJoin('ED', 'ET_CHAIRS', 'ECH', 'ED.CHAIR = ECH.OID')
            ->where('EG.OID = :GROUPID')
            ->andWhere('ECSEM.OID = :SEMESTERID')
            ->setParameter('GROUPID', $groupId)
            ->setParameter('SEMESTERID', $semesterId)
            ->execute();

        $subjectList = [];
        while($subject = $result->fetch())
        {
            $subjectItem = new AcademicSubject();
            $subjectItem->setSubjectName($subject['DISCIPLINE_NAME'] ?
                $this->stringConverter->capitalize($subject['DISCIPLINE_NAME']) : null);
            $subjectItem->setChairName($subject['CHAIR_NAME'] ?
                $this->stringConverter->capitalize($subject['CHAIR_NAME']) : null);
            $subjectItem->setSubjectId($subject['DISCIPLINE_ID']);
            $subjectList[] = $subjectItem;
        }

        return $subjectList;
    }
}