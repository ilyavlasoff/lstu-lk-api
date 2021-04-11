<?php

namespace App\Repository;

use App\Model\Grouping\Day;
use App\Model\Mapping\Discipline;
use App\Model\Mapping\Exam;
use App\Model\Mapping\Person;
use App\Model\Mapping\Teacher;
use App\Model\Mapping\TimetableItem;
use App\Service\StringConverter;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;

class TimetableRepository
{
    private $entityManager;
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        $this->entityManager = $entityManager;
        $this->stringConverter = $stringConverter;
    }

    public function getTimetableItems(string $groupId, string $semesterId, array $weekColorCodes): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select('ED.OID DISCIPLINE_OID, ED.NAME AS DISCIPLINE, NP.OID AS TCH_PERSON,
            TTCH.OID AS TEACHER_OID, NP.FNAME AS TCH_FNAME, NP.FAMILY AS TCH_LNAME, NP.MNAME AS TTCH_PTR,
            EDL.NAME AS TCH_POST, EW.OID AS WEEK, EDW.OID AS WEEKDAY, ECT.BEGIN AS TIME_START, ECT.END AS TIME_END, 
            TTK.VALUE AS CLASS_TYPE, TT.HALL AS ROOM, EK.NAME AS CAMPUS')
            ->from('ET_GROUPS', 'EG')
            ->innerJoin('EG', 'ET_RCONTINGENTS', 'ER', 'EG.OID = ER.G')
            ->innerJoin('ER', 'ET_CSEMESTERS', 'EC','ER.CSEMESTER = EC.OID')
            ->innerJoin('EC', 'T_TIMETABLE', 'TT',
                $queryBuilder->expr()->andX('EC.OID = TT.CSEMESTER', 'EG.OID = TT.G'))
            ->innerJoin('TT', 'ET_DISCIPLINES', 'ED', 'ED.OID = TT.DISCIPLINE')
            ->innerJoin('TT', 'ET_DAYWEEK', 'EDW', 'EDW.OID = TT.DAY')
            ->innerJoin('TT', 'ET_CLASSTIME', 'ECT', 'ECT.OID = TT.ROOM_TIME')
            ->leftJoin('TT', 'T_TKINDS', 'TTK', 'TTK.OID = TT.TKIND')
            ->innerJoin('TT', 'ET_WEEKCOLOR', 'EW', 'EW.OID = TT.WEEKCOLOR')
            ->leftJoin('TT', 'T_TEACHERS', 'TTCH', 'TT.TEACHER = TTCH.OID')
            ->leftJoin('TTCH', 'NPERSONS', 'NP', 'TTCH.C_OID = NP.OID')
            ->leftJoin('TTCH', 'ET_DOLTIMETABLE', 'EDL', 'TTCH.DOLTIMETABLE = EDL.OID')
            ->leftJoin('TT', 'ET_ROOMS', 'ERMS', 'TT.HALL = ERMS.CODE')
            ->leftJoin('ERMS', 'ET_KORPUS', 'EK', 'ERMS.KORPUS = EK.OID')
            ->where('EW.CODE IN (:COLORS)')
            ->andWhere('TT.G = :GROUP')
            ->andWhere('TT.CSEMESTER = :SEM')
            ->setParameter('COLORS', $weekColorCodes, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
            ->setParameter('GROUP', $groupId)
            ->setParameter('SEM', $semesterId)
            ->execute();

        $disciplines = [];
        while($timetableItem = $result->fetch()) {
            $discipline = new Discipline();
            $discipline->setId($timetableItem['DISCIPLINE_OID']);
            $discipline->setName($this->stringConverter->capitalize($timetableItem['DISCIPLINE']));

            $teacherPerson = new Person();
            $teacherPerson->setUoid($timetableItem['TCH_PERSON']);
            $teacherPerson->setLname($this->stringConverter->capitalize($timetableItem['TCH_LNAME']));
            $teacherPerson->setFname($this->stringConverter->capitalize($timetableItem['TCH_FNAME']));
            $teacherPerson->setPatronymic($this->stringConverter->capitalize($timetableItem['TTCH_PTR']));

            $teacher = new Teacher();
            $teacher->setId($timetableItem['TEACHER_OID']);
            $teacher->setPerson($teacherPerson);
            $teacher->setPosition($timetableItem['TCH_POST']);

            $tti = new TimetableItem();
            $tti->setBeginTime($timetableItem['TIME_START']);
            $tti->setEndTime($timetableItem['TIME_END']);
            $tti->setCampus($timetableItem['CAMPUS']);
            $tti->setRoom($timetableItem['ROOM']);
            $tti->setLessonType($timetableItem['CLASS_TYPE']);
            $tti->setDiscipline($discipline);
            $tti->setTeacher($teacher);

            $week = $timetableItem['WEEK'];
            $weekday = $timetableItem['WEEKDAY'];
            $disciplines[$week][$weekday][] = $tti;
        }

        return $disciplines;
    }

    public function getDays(): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select('DW.OID, DW.NUM, DW.NAME')
            ->from('ET_DAYWEEK', 'DW')
            ->execute();

        $weekDays = [];
        while($day = $result->fetch()) {
            $weekDay = new Day();
            $weekDay->setId($day['OID']);
            $weekDay->setName($day['NAME']);
            $weekDay->setNumber($day['NUM']);
            $weekDays[] = $weekDay;
        }

        return $weekDays;
    }

    public function getWeeksByName(string $name): array
    {
        if($name === 'green') {
            $weekDbNames = ['Всегда', 'Зеленая'];
        } elseif ($name === 'white') {
            $weekDbNames = ['Всегда', 'Белая'];
        } else {
            return [];
        }

        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('WC.CODE')
            ->from('ET_WEEKCOLOR', 'WC')
            ->where('WC.NAME IN (:WNAMES)')
            ->setParameter('WNAMES', $weekDbNames, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
            ->execute();
        return $result->fetchAll(FetchMode::COLUMN);
    }

    public function getExamsTimetable(string $groupId, string $semesterId): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select("ED.OID DISC_ID, ED.NAME DISCIPLINE, CASE WHEN EE.LTEACHER IS NOT NULL THEN
                COALESCE(EDL.ABBR, 'преп.') || TT.SURNAME || ' ' || SUBSTR(TT.FIRSTNAME, 0, 1) || '.' || SUBSTR(TT.PATRONYMIC, 0, 1) ELSE EE.TEACHER END AS TEACHER,
                EE.EDATE AS DT, EE.ETIME AS TM, EE.ROOM, EK.NAME AS CAMPUS")
            ->from('ET_EEXAMS', 'EE')
            ->innerJoin('EE', 'ET_DISCIPLINES', 'ED', 'EE.DISCIPLINE = ED.OID')
            ->leftJoin('EE', 'T_TEACHERS', 'TT', 'EE.LTEACHER = TT.OID')
            ->leftJoin('TT', 'ET_DOLTIMETABLE', 'EDL', 'TT.DOLTIMETABLE = EDL.OID')
            ->leftJoin('EE', 'ET_ROOMS', 'ER', 'EE.ROOM = ER.CODE')
            ->leftJoin('ER', 'ET_KORPUS', 'EK', 'ER.KORPUS = EK.OID')
            ->where('EE.G = :GROUP')
            ->andWhere('EE.CSEMESTER = :SEM')
            ->setParameter('GROUP', $groupId)
            ->setParameter('SEM', $semesterId)
            ->execute();

        $exams = [];
        while($examRow = $result->fetch()) {
            $exam = new Exam();
            $exam->setTeacherName($examRow['TEACHER']);
            $exam->setRoom($examRow['ROOM']);
            $exam->setCampus($examRow['CAMPUS']);

            /** @var \DateTime|null $examDate */
            $examDate = $examRow['DT'];
            $examTime = $examRow['TM'];
            if($examDate) {
                $examDate = new \DateTime($examDate);
                $examDividerPos = strpos($examTime, ':');
                $examHrs = (int)substr($examTime,0, $examDividerPos);
                $examMins = (int)substr($examTime, $examDividerPos + 1,strlen($examTime) - $examDividerPos);
                $examDate = $examDate->modify("+ $examHrs hours")->modify("+ $examMins minutes");
            }
            $exam->setExamTime($examDate);

            $discipline = new Discipline();
            $discipline->setName($examRow['DISCIPLINE']
                ? $this->stringConverter->capitalize($examRow['DISCIPLINE'])
                : $examRow['DISCIPLINE']);
            $discipline->setId($examRow['DISC_ID']);
            $exam->setDiscipline($discipline);

            $exams[] = $exam;
        }

        return $exams;
    }
}