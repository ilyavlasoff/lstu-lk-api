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
        $sql = 'SELECT EDIS.OID as DISCIPLINE_ID, EDIS.NAME AS DISCIPLINE_NAME, ECH.VALUE AS CHAIR_NAME ' .
                'FROM ET_RCONTINGENTS ER ' .
                'INNER JOIN ET_GROUPS EG ON ER.G = EG.OID ' .
                'INNER JOIN ET_CSEMESTERS ECSEM ON ER.CSEMESTER = ECSEM.OID ' .
                'INNER JOIN ET_CURRICULUMS EC ON ER.PLAN = EC.OID ' .
                'INNER JOIN ET_DSPLANS ED ON EC.OID = ED.EPLAN AND ED.SEMESTER = ER.SEMESTER ' .
                'INNER JOIN ET_DISCIPLINES EDIS ON ED.DISCIPLINE = EDIS.OID ' .
                'LEFT JOIN ET_CHAIRS ECH ON ED.CHAIR = ECH.OID ' .
                'WHERE EG.OID = :GROUPID AND ECSEM.OID = :SEMESTERID';

        $query = $this->entityManager->getConnection()->prepare($sql);
        $query->bindValue('GROUPID', $groupId);
        $query->bindValue('SEMESTERID', $semesterId);
        $query->execute();

        $subjectList = [];
        while($subject = $query->fetch())
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