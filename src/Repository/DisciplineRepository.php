<?php

namespace App\Repository;

use App\Model\Mapping\Discipline;
use App\Model\Mapping\Teacher;
use App\Model\Mapping\TimetableItem;
use Doctrine\ORM\EntityManagerInterface;

class DisciplineRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getTeachersByDiscipline(string $discipline, string $group, string $semester)
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('TT.TEACHER AS TCH_OID, TTCH.FIRSTNAME AS FNAME, TTCH.SURNAME AS LNAME, 
            TTCH.PATRONYMIC AS PTR, ED.ABBR AS TEACHER_POST, EDIS.OID AS DIS_ID, EDIS.NAME AS DISCIPLINE, T.VALUE AS LESSON_TYPE')
            ->from('T_TIMETABLE', 'TT')
            ->innerJoin('TT', 'ET_DISCIPLINES', 'EDIS', 'TT.DISCIPLINE = EDIS.OID')
            ->leftJoin('TT', 'T_TKINDS', 'T', 'TT.TKIND = T.OID')
            ->innerJoin('TT', 'T_TEACHERS', 'TTCH', 'TT.TEACHER = TTCH.OID')
            ->leftJoin('TTCH', 'ET_DOLTIMETABLE', 'ED', 'TTCH.DOLTIMETABLE = ED.OID')
            ->where('TT.CSEMESTER = :SEMESTER')
            ->andWhere('TT.G = :GROUP')
            ->andWhere('TT.DISCIPLINE = :DISCIPLINE')
            ->groupBy('TT.TEACHER, TTCH.FIRSTNAME, TTCH.SURNAME, TTCH.PATRONYMIC, ED.ABBR, EDIS.OID, EDIS.NAME, T.VALUE')
            ->setParameter('SEMESTER', $semester)
            ->setParameter('GROUP', $group)
            ->setParameter('DISCIPLINE', $discipline)
            ->execute();

        $disciplineTeachers = [];
        while($teacherRow = $result->fetch()) {
            $discipline = new Discipline();
            $discipline->setId($teacherRow['DIS_ID']);
            $discipline->setName($teacherRow['DISCIPLINE']);

            $teacher = new Teacher();
            $teacher->setId($teacherRow['TCH_OID']);
            $teacher->setPosition($teacherRow['TEACHER_POST']);
            $teacher->setFname($teacherRow['FNAME']);
            $teacher->setLname($teacherRow['LNAME']);
            $teacher->setPatronymic($teacherRow['PTR']);

            $disciplineTeacher = new TimetableItem();
            $disciplineTeacher->setDiscipline($discipline);
            $disciplineTeacher->setTeacher($teacher);
            $disciplineTeacher->setLessonType($teacherRow['LESSON_TYPE']);

            $disciplineTeachers[] = $disciplineTeacher;
        }

        return $disciplineTeachers;
    }


}