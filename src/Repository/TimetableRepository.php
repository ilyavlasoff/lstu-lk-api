<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\DTO\Day;
use App\Model\DTO\Week;
use App\Model\DTO\Discipline;
use App\Model\DTO\Exam;
use App\Model\DTO\Person;
use App\Model\DTO\Teacher;
use App\Model\DTO\TimetableItem;
use App\Model\DTO\Timetable;
use App\Service\StringConverter;
use Doctrine\DBAL\Exception;
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

    /**
     * @param string $group
     * @param string $semester
     * @param string|null $weekColor
     * @param string|null $discipline
     * @param string|null $teacher
     * @return Timetable
     * @throws Exception|\Doctrine\DBAL\Driver\Exception
     */
    public function getTimetable(
        string $group,
        string $semester,
        string $weekColor = null,
        string $discipline = null,
        string $teacher = null
    ): Timetable {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder->select('ED.OID DISCIPLINE_OID, ED.NAME AS DISCIPLINE, NP.OID AS TCH_PERSON,
            TTCH.OID AS TEACHER_OID, NP.FNAME AS TCH_FNAME, NP.FAMILY AS TCH_LNAME, NP.MNAME AS TTCH_PTR,
            EDL.NAME AS TCH_POST, EW.OID AS WEEK, EW.NAME AS WEEK_NAME, EDW.OID AS WEEKDAY, ECT.BEGIN AS TIME_START, 
            ECT.END AS TIME_END, ECT.NUM AS LESSON_NUM, TTK.VALUE AS CLASS_TYPE, TT.HALL AS ROOM, EK.NAME AS CAMPUS')
            ->from('ET_GROUPS', 'EG')
            ->innerJoin('EG', 'ET_RCONTINGENTS', 'ER', 'EG.OID = ER.G')
            ->innerJoin('ER', 'ET_CSEMESTERS', 'EC','ER.CSEMESTER = EC.OID')
            ->innerJoin(
                'EC',
                "(SELECT ITT.*, W2.OID AS COLOR2 FROM T_TIMETABLE ITT
                JOIN ET_WEEKCOLOR IEW ON ITT.WEEKCOLOR = IEW.OID
                CROSS JOIN (SELECT OID FROM ET_WEEKCOLOR WHERE NAME IN ('??????????','??????????????')) W2
                WHERE IEW.NAME = '????????????'
                UNION ALL
                SELECT I2TT.*, I2EW.OID AS COLOR2 FROM T_TIMETABLE I2TT
                JOIN ET_WEEKCOLOR I2EW on I2TT.WEEKCOLOR = I2EW.OID
                WHERE I2EW.NAME <> '????????????')",
                'TT',
                'EC.OID = TT.CSEMESTER AND TT.G = EG.OID'
            )
            ->innerJoin('TT', 'ET_DISCIPLINES', 'ED', 'ED.OID = TT.DISCIPLINE')
            ->innerJoin('TT', 'ET_DAYWEEK', 'EDW', 'EDW.OID = TT.DAY')
            ->innerJoin('TT', 'ET_CLASSTIME', 'ECT', 'ECT.OID = TT.ROOM_TIME')
            ->leftJoin('TT', 'T_TKINDS', 'TTK', 'TTK.OID = TT.TKIND')
            ->innerJoin('TT', 'ET_WEEKCOLOR', 'EW', 'EW.OID = TT.COLOR2')
            ->leftJoin('TT', 'T_TEACHERS', 'TTCH', 'TT.TEACHER = TTCH.OID')
            ->leftJoin('TTCH', 'NPERSONS', 'NP', 'TTCH.C_OID = NP.OID')
            ->leftJoin('TTCH', 'ET_DOLTIMETABLE', 'EDL', 'TTCH.DOLTIMETABLE = EDL.OID')
            ->leftJoin('TT', 'ET_ROOMS', 'ERMS', 'TT.HALL = ERMS.CODE')
            ->leftJoin('ERMS', 'ET_KORPUS', 'EK', 'ERMS.KORPUS = EK.OID')
            ->where('TT.G = :GROUP')
            ->andWhere('TT.CSEMESTER = :SEM');
        if($weekColor) {
            $queryBuilder
                ->andWhere('EW.CODE = :COLOR')
                ->setParameter('COLOR', $weekColor);
        };
        if($discipline) {
            $queryBuilder
                ->andWhere('ED.OID = :DISCIPLINE')
                ->setParameter('DISCIPLINE', $discipline);
        }
        if($teacher) {
            $queryBuilder
                ->andWhere('TTCH.OID = :TCHR')
                ->setParameter('TCHR', $teacher);
        }
        $result = $queryBuilder
            ->setParameter('GROUP', $group)
            ->setParameter('SEM', $semester)
            ->execute();

        $timetableUnmapped = [];
        while($timetableItem = $result->fetchAssociative()) {
            $tti = new TimetableItem();
            $tti->setLessonNumber($timetableItem['LESSON_NUM']);
            $tti->setBeginTime($timetableItem['TIME_START']);
            $tti->setEndTime($timetableItem['TIME_END']);
            $tti->setCampus($timetableItem['CAMPUS']);
            $tti->setRoom($timetableItem['ROOM']);
            $tti->setLessonType($timetableItem['CLASS_TYPE']);

            $discipline = new Discipline();
            $discipline->setId($timetableItem['DISCIPLINE_OID']);
            $discipline->setName($this->stringConverter->capitalize($timetableItem['DISCIPLINE']));
            $tti->setDiscipline($discipline);

            if($timetableItem['TEACHER_OID']) {
                $teacherPerson = new Person();
                $teacherPerson->setUoid($timetableItem['TCH_PERSON']);
                $teacherPerson->setLname($this->stringConverter->capitalize($timetableItem['TCH_LNAME']));
                $teacherPerson->setFname($this->stringConverter->capitalize($timetableItem['TCH_FNAME']));
                $teacherPerson->setPatronymic($this->stringConverter->capitalize($timetableItem['TTCH_PTR']));

                $teacher = new Teacher();
                $teacher->setId($timetableItem['TEACHER_OID']);
                $teacher->setPerson($teacherPerson);
                $teacher->setPosition($timetableItem['TCH_POST']);
                $tti->setTeacher($teacher);
            }

            $coloredWeek = new \App\Model\QueryParam\Week();
            $coloredWeek->createByWeekNameValue($timetableItem['WEEK_NAME']);

            $week = $coloredWeek->getWeekCode();
            $weekday = $timetableItem['WEEKDAY'];
            $timetableUnmapped[$week][$weekday][] = $tti;
        }

        $timetable = new Timetable();
        $timetable->setGroupId($group);
        $timetable->setGroupName('group');

        $timetableWeeks = [];
        foreach ($timetableUnmapped as $weekName => $timetableWeek) {
            $week = new Week();
            $week->setType($weekName);
            $week->setCurrent(false);
            $weekDays = [];
            foreach ($timetableWeek as $timetableDay => $dayLessons) {
                $day = $this->getDayById($timetableDay);

                usort($dayLessons, static function (TimetableItem $f, TimetableItem $s) {
                    return $f->getLessonNumber() > $s->getLessonNumber();
                });

                $day->setLessons($dayLessons);
                $weekDays[] = $day;
            }

            usort($weekDays, static function (Day $f, Day $s) {
                return $f->getNumber() > $s->getNumber();
            });

            $week->setDays($weekDays);
            $timetableWeeks[] = $week;
        }
        $timetable->setWeeks($timetableWeeks);

        return $timetable;
    }

    /**
     * @param string $dayId
     * @return Day
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getDayById(string $dayId): Day
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select('DW.OID, DW.NUM, DW.VALUE')
            ->from('ET_DAYWEEK', 'DW')
            ->where('DW.OID = :DWID')
            ->setParameter('DWID', $dayId)
            ->execute()
            ->fetchAllAssociative();

        if(!count($result)) {
            throw new NotFoundException('Day');
        }

        $day = $result[0];
        $weekDay = new Day();
        $weekDay->setId($day['OID']);
        $weekDay->setName($day['VALUE']);
        $weekDay->setNumber($day['NUM']);

        return $weekDay;
    }

    /**
     * @param string $weekName
     * @return mixed
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getWeekByName(string $weekName)
    {
        $result = $this->entityManager->getConnection()->createQueryBuilder()
            ->select('WC.CODE')
            ->from('ET_WEEKCOLOR', 'WC')
            ->where('WC.NAME = :WEEKNAME')
            ->setParameter('WEEKNAME', $weekName)
            ->execute();

        $weekData = $result->fetchAllAssociative();

        if(!$weekData) {
            throw new NotFoundException('Week');
        }

        return $weekData[0]['CODE'];
    }

    /**
     * @param string $groupId
     * @param string $semesterId
     * @return array
     * @throws Exception
     * @throws \Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getExamsTimetable(string $groupId, string $semesterId): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select(
                "ED.OID DISC_ID, 
                ED.NAME DISCIPLINE, 
                TT.SURNAME AS TCH_SURNAME,
                TT.FIRSTNAME AS TCH_FIRSTNAME,
                TT.PATRONYMIC AS TCH_PATRONYMIC,
                EE.TEACHER AS UK_TEACHER,
                EE.EDATE AS DT, 
                EE.ETIME AS TM, 
                EE.ROOM, 
                EK.NAME AS CAMPUS")
            ->from('ET_EEXAMS', 'EE')
            ->innerJoin('EE', 'ET_DISCIPLINES', 'ED', 'EE.DISCIPLINE = ED.OID')
            ->leftJoin('EE', 'T_TEACHERS', 'TT', 'EE.LTEACHER = TT.OID')
            ->leftJoin('TT', 'ET_DOLTIMETABLE', 'EDL', 'TT.DOLTIMETABLE = EDL.OID')
            ->leftJoin('EE', 'ET_ROOMS', 'ER', 'EE.ROOM = ER.CODE')
            ->leftJoin('ER', 'ET_KORPUS', 'EK', 'ER.KORPUS = EK.OID')
            ->where('EE.G = :GROUP')
            ->andWhere('EE.CSEMESTER = :SEM')
            ->setParameter('GROUP', $groupId)
            ->setParameter('SEM', $semesterId);
        $result = $queryBuilder->execute();

        $exams = [];
        while($examRow = $result->fetchAssociative()) {
            $exam = new Exam();
            if(($tName = $examRow['TCH_FIRSTNAME']) && ($tSurname = $examRow['TCH_SURNAME'])) {
                $teacherAbbrName = $this->stringConverter->createAbbreviatedName($tName, $tSurname, $examRow['TCH_PATRONYMIC']);
            } else {
                $teacherAbbrName = $examRow['UK_TEACHER'];
            }
            $exam->setTeacherName($teacherAbbrName);
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