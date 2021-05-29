<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\Chair;
use App\Model\DTO\Discipline;
use App\Model\DTO\DiscussionMessage;
use App\Model\DTO\ExternalLink;
use App\Model\DTO\Faculty;
use App\Model\DTO\Person;
use App\Model\DTO\StudentWork;
use App\Model\DTO\Teacher;
use App\Model\DTO\TeachingMaterial;
use App\Model\DTO\TimetableItem;
use App\Model\DTO\WorkAnswer;
use App\Model\QueryParam\SendingDiscussionMessage;
use App\Service\StringConverter;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Type\Type;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Json;

class DisciplineRepository extends AbstractRepository
{
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, DocumentManager $documentManager, StringConverter $stringConverter)
    {
        parent::__construct($entityManager, $documentManager);
        $this->stringConverter = $stringConverter;
    }

    /**
     * @param string $discipline
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function isDisciplineExists(string $discipline): bool {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('COUNT(*) AS CNT')
            ->from('ET_DISCIPLINES', 'EDIS')
            ->where('EDIS.OID = :DISCIPLINE')
            ->setParameter('DISCIPLINE', $discipline)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
    }

    /**
     * @param string $discipline
     * @return Discipline
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getDiscipline(string $discipline): Discipline
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        $disciplineRows = $queryBuilder
            ->select('EDIS.OID AS DIS_ID, 
                EDIS.NAME AS DIS_NAME, 
                EDISCAT.NAME AS DISCAT_NAME, 
                ECH.OID AS CH_ID, 
                ECH.VALUE AS CH_NAME,
                ED.OID AS FAC_ID,
                ED.NAME AS FAC_ABBR,
                ED.VALUE AS FAC_NAME
            ')
            ->from('ET_DISCIPLINES', 'EDIS')
            ->leftJoin('EDIS', 'ET_CHAIRS', 'ECH', 'EDIS.CHAIR = ECH.OID')
            ->leftJoin('ECH', 'ET_DEANS', 'ED', 'ECH.FACULTY = ED.OID')
            ->leftJoin('EDIS', 'ET_DISCATEGORIES', 'EDISCAT', 'EDIS.DISCATEGORY = EDISCAT.OID')
            ->where('EDIS.OID = :DISCIPLINE')
            ->setParameter('DISCIPLINE', $discipline)
            ->execute()
            ->fetchAllAssociative();

        if(count($disciplineRows) !== 1) {
            throw new NotFoundException('Discipline');
        }

        $disciplineData = $disciplineRows[0];

        $discipline = new Discipline();
        $discipline->setId($disciplineData['DIS_ID']);
        $discipline->setName($this->stringConverter->capitalize($disciplineData['DIS_NAME']));
        $discipline->setCategory($disciplineData['DISCAT_NAME']);

        $chair = new Chair();
        $chair->setId($disciplineData['CH_ID']);
        $chair->setChairName($this->stringConverter->capitalize($disciplineData['CH_NAME']));

        $faculty = new Faculty();
        $faculty->setId($disciplineData['FAC_ID']);
        $faculty->setFacName($this->stringConverter->capitalize($disciplineData['FAC_NAME']));
        $faculty->setFacCode($disciplineData['FAC_ABBR']);

        $chair->setFaculty($faculty);
        $discipline->setChair($chair);

        return $discipline;
    }

    /**
     * @param string $discipline
     * @param string $group
     * @param string $semester
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getTeachersByDiscipline(string $discipline, string $group, string $semester): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('TT.TEACHER AS TCH_OID, NP.OID AS TCH_PERSON, TTCH.FIRSTNAME AS FNAME, TTCH.SURNAME AS LNAME, 
            TTCH.PATRONYMIC AS PTR, ED.ABBR AS TEACHER_POST, EDIS.OID AS DIS_ID, EDIS.NAME AS DISCIPLINE, T.VALUE AS LESSON_TYPE')
            ->from('T_TIMETABLE', 'TT')
            ->innerJoin('TT', 'ET_DISCIPLINES', 'EDIS', 'TT.DISCIPLINE = EDIS.OID')
            ->leftJoin('TT', 'T_TKINDS', 'T', 'TT.TKIND = T.OID')
            ->innerJoin('TT', 'T_TEACHERS', 'TTCH', 'TT.TEACHER = TTCH.OID')
            ->innerJoin('TTCH', 'NPERSONS', 'NP', 'TTCH.C_OID = NP.OID')
            ->leftJoin('TTCH', 'ET_DOLTIMETABLE', 'ED', 'TTCH.DOLTIMETABLE = ED.OID')
            ->where('TT.CSEMESTER = :SEMESTER')
            ->andWhere('TT.G = :GROUP')
            ->andWhere('TT.DISCIPLINE = :DISCIPLINE')
            ->groupBy('TT.TEACHER, TTCH.FIRSTNAME, NP.OID, TTCH.SURNAME, TTCH.PATRONYMIC, ED.ABBR, EDIS.OID, EDIS.NAME, T.VALUE')
            ->setParameter('SEMESTER', $semester)
            ->setParameter('GROUP', $group)
            ->setParameter('DISCIPLINE', $discipline);
        $result = $queryBuilder->execute();

        $disciplineTeachers = [];
        while($teacherRow = $result->fetchAssociative()) {
            $discipline = new Discipline();
            $discipline->setId($teacherRow['DIS_ID']);
            $discipline->setName($this->stringConverter->capitalize($teacherRow['DISCIPLINE']));

            $teacherPerson = new Person();
            $teacherPerson->setUoid($teacherRow['TCH_PERSON']);
            $teacherPerson->setFname($teacherRow['FNAME']);
            $teacherPerson->setLname($teacherRow['LNAME']);
            $teacherPerson->setPatronymic($teacherRow['PTR']);

            $teacher = new Teacher();
            $teacher->setId($teacherRow['TCH_OID']);
            $teacher->setPosition($teacherRow['TEACHER_POST']);
            $teacher->setPerson($teacherPerson);

            $disciplineTeacher = new TimetableItem();
            $disciplineTeacher->setDiscipline($discipline);
            $disciplineTeacher->setTeacher($teacher);
            $disciplineTeacher->setLessonType($teacherRow['LESSON_TYPE']);

            $disciplineTeachers[] = $disciplineTeacher;
        }

        return $disciplineTeachers;
    }

    /**
     * @param string $groupId
     * @param string $semesterId
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getDisciplinesBySemester(string $groupId, string $semesterId): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        //$result = $queryBuilder->select('EDIS.OID as DISCIPLINE_ID, EDIS.NAME AS DISCIPLINE_NAME, ECH.VALUE AS CHAIR_NAME')
        $queryBuilder->select('EDIS.OID as DISCIPLINE_ID, EDIS.NAME AS DISCIPLINE_NAME')
            ->from('ET_RCONTINGENTS', 'ER')
            ->innerJoin('ER', 'ET_GROUPS', 'EG', 'ER.G = EG.OID')
            ->innerJoin('ER', 'ET_CSEMESTERS', 'ECSEM', 'ER.CSEMESTER = ECSEM.OID')
            ->innerJoin('ER', 'ET_CURRICULUMS', 'EC', 'ER.PLAN = EC.OID')
            ->innerJoin('EC', 'ET_DSPLANS', 'ED', $queryBuilder->expr()->and('EC.OID = ED.EPLAN', 'ED.SEMESTER = ER.SEMESTER'))
            ->innerJoin('ED', 'ET_DISCIPLINES', 'EDIS', 'ED.DISCIPLINE = EDIS.OID')
            //->leftJoin('ED', 'ET_CHAIRS', 'ECH', 'ED.CHAIR = ECH.OID')
            ->where('EG.OID = :GROUPID')
            ->andWhere('ECSEM.OID = :SEMESTERID')
            ->setParameter('GROUPID', $groupId)
            ->setParameter('SEMESTERID', $semesterId);
        $result = $queryBuilder->execute();

        $subjectList = [];
        while($subject = $result->fetchAssociative())
        {
            $subjectItem = new Discipline();
            $subjectItem->setName($subject['DISCIPLINE_NAME'] ?
                $this->stringConverter->capitalize($subject['DISCIPLINE_NAME']) : null);
            /*$subjectItem->setChairName($subject['CHAIR_NAME'] ?
                $this->stringConverter->capitalize($subject['CHAIR_NAME']) : null);*/
            $subjectItem->setId($subject['DISCIPLINE_ID']);
            $subjectList[] = $subjectItem;
        }

        return $subjectList;
    }
}