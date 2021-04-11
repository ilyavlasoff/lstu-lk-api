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

    public function getTimetableItems(string $groupId, string $semesterId, array $weekColorCodes): array
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder->select('ED.OID DISCIPLINE_OID, ED.NAME AS DISCIPLINE,
            TTCH.OID AS TEACHER_OID, TTCH.FIRSTNAME AS TCH_FNAME, TTCH.SURNAME AS TCH_LNAME, TTCH.PATRONYMIC AS TTCH_PTR,
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
            ->leftJoin('TTCH', 'ET_DOLTIMETABLE', 'EDL', 'TTCH.DOLTIMETABLE = EDL.OID')
            ->leftJoin('TT', 'ET_ROOMS', 'ERMS', 'TT.HALL = ERMS.CODE')
            ->leftJoin('ERMS', 'ET_KORPUS', 'EK', 'ERMS.KORPUS = EK.OID')
            ->where($queryBuilder->expr()->in('EW.CODE', $weekColorCodes))
            ->andWhere('TT.G', ':GROUP')
            ->andWhere('TT.CSEMESTER', ':SEM')
            ->setParameter('GROUP', $groupId)
            ->setParameter('SEM', $semesterId)
            ->execute();

        $disciplines = [];
        while($timetableItem = $result->fetch()) {
            $discipline = new Discipline();
            $discipline->setId($timetableItem['DISCIPLINE_OID']);
            $discipline->setName($timetableItem['DISCIPLINE']);

            $teacher = new Teacher();
            $teacher->setId($timetableItem['TEACHER_OID']);
            $teacher->setFname($timetableItem['TCH_FNAME']);
            $teacher->setLname($timetableItem['TCH_LNAME']);
            $teacher->setPatronymic($timetableItem['TTCH_PTR']);
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
            $disciplines[$week][$weekday] = $tti;
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
            ->select('WC.COLOR')
            ->from('ET_WEEKCOLOR', 'WC')
            ->where($queryBuilder->expr()->in('WC.NAME', $weekDbNames))
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
                $examHrs = (int)substr($examTime, $examDividerPos);
                $examMins = (int)substr($examTime, $examDividerPos,strlen($examTime) - $examDividerPos);
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