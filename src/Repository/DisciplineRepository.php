<?php

namespace App\Repository;

use App\Model\Mapping\Discipline;
use App\Model\Mapping\DiscussionAttachment;
use App\Model\Mapping\DiscussionExternalLink;
use App\Model\Mapping\DiscussionMessage;
use App\Model\Mapping\Person;
use App\Model\Mapping\StudentWork;
use App\Model\Mapping\Teacher;
use App\Model\Mapping\TimetableItem;
use App\Model\Mapping\WorkAnswer;
use App\Model\Mapping\WorkAttachment;
use App\Service\StringConverter;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;

class DisciplineRepository
{
    private $entityManager;
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        $this->entityManager = $entityManager;
        $this->stringConverter = $stringConverter;
    }

    public function isDisciplineExists(string $discipline): bool {
        $dis = $this->entityManager->getConnection()->createQueryBuilder()
            ->select('EDIS.OID')
            ->from('ET_DISCIPLINES', 'EDIS')
            ->where('EDIS.OID = :DISCIPLINE')
            ->setParameter('DISCIPLINE', $discipline)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);

        return count($dis) === 1;
    }

    public function getTeachersByDiscipline(string $discipline, string $group, string $semester): array
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
            $discipline->setName($this->stringConverter->capitalize($teacherRow['DISCIPLINE']));

            $teacherPerson = new Person();
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

    public function getDisciplineChatMessagesCount(string $group, string $semester, string $discipline): int
    {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('COUNT(EM.OID) AS COUNT')
            ->from('ET_MSG_LK', 'EM')
            ->where('EM.G = :GROUP')
            ->andWhere('EM.DISCIPLINE = :DISC')
            ->andWhere('EM.CSEMESTER = :SEM')
            ->setParameter('GROUP', $group)
            ->setParameter('SEM', $semester)
            ->setParameter('DISC', $discipline)
            ->execute()
            ->fetchAll();
        return $result[0]['COUNT'];
    }

    public function getDisciplineChatMessages(
        string $semester,
        string $discipline,
        string $group,
        int $offset,
        int $limit
    ): array {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select("
                EM.OID AS MSG_ID,
                N.OID AS AUTHOR_ID,
                N.FNAME AS AUTHOR_FNAME,
                N.FAMILY AS AUTHOR_LNAME,
                N.MNAME AS AUTHOR_PTR,
                EM.CREATED AS SEND_TIME,
                EM.MSG,
                ROUND(DBMS_LOB.GETLENGTH(EM.DOC)/1024) AS DOC_KB,
                EM.FILE\$DOC AS DOC_NAME,
                EM.EXTLINK AS LOCATION,
                EM.TEXTLINK AS LINK_TEXT
            ")
            ->from('ET_MSG_LK', 'EM')
            ->innerJoin('EM', 'NPERSONS', 'N', 'EM.AUTHOR = N.OID')
            ->where('EM.G = :GROUP')
            ->andWhere('EM.DISCIPLINE = :DISC')
            ->andWhere('EM.CSEMESTER = :SEM')
            ->orderBy('EM.CREATED', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $response = $queryBuilder
            ->setParameter('GROUP', $group)
            ->setParameter('DISC', $discipline)
            ->setParameter('SEM', $semester)
            ->execute();

        $discussionMessages = [];
        while($messageRow = $response->fetch()) {
            $message = new DiscussionMessage();
            $message->setId($messageRow['MSG_ID']);
            if($createDate = $messageRow['SEND_TIME']) {
                $message->setCreated(new \DateTime($createDate));
            }
            $message->setMsg($messageRow['MSG']);

            $sender = new Person();
            $sender->setUoid($messageRow['AUTHOR_ID']);
            if ($lName = $messageRow['AUTHOR_LNAME']) {
                $sender->setLname($this->stringConverter->capitalize($lName));
            }
            if($fName = $messageRow['AUTHOR_FNAME']) {
                $sender->setFname($this->stringConverter->capitalize($fName));
            }
            if($patronymic = $messageRow['AUTHOR_PTR']) {
                $sender->setPatronymic($this->stringConverter->capitalize($patronymic));
            }

            $message->setSender($sender);

            if(($attachmentName = $messageRow['DOC_NAME']) && ($attachmentSize = $messageRow['DOC_KB'])) {
                $attachment = new DiscussionAttachment();
                $attachment->setAttachmentId($messageRow['MSG_ID']);
                $attachment->setFileName($attachmentName);
                $attachment->setFileSize($attachmentSize);
                $message->setAttachments([$attachment]);
            }

            if(($externalLink = $messageRow['LINK_TEXT']) && ($externalLocation = $messageRow['LOCATION'])) {
                $extLink = new DiscussionExternalLink();
                $extLink->setLinkLocation($externalLocation);
                $extLink->setLinkText($externalLink);
                $message->setExternalLinks([$extLink]);
            }

            $discussionMessages[] = $message;
        }

        return $discussionMessages;
    }

    public function getStudentWorksList(
        string $semester,
        string $discipline,
        string $group,
        string $contingent
    ) {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select("
                ESW.OID AS WORK_ID,
                N.OID AS TCH_POID,
                N.FNAME AS TCH_NAME,
                N.FAMILY AS TCH_SURNAME,
                N.MNAME AS TCH_PTR,
                TT.OID AS TCH_ID,
                ED.NAME AS TCH_POST,
                T.VALUE AS WORK_TYPE,
                ESW.NAME AS WORK_NAME,
                ESW.THEME AS WORK_THEME,
                ESW.MAX_BALL AS SCORE,
                W_ANS.OID AS ATT_ID,
                W_ANS.DOC_SIZE AS ATT_DOC_KB,
                W_ANS.DOC_NAME AS ATT_FILENAME,
                W_ANS.DOC_TITLE AS ATT_TITLE,
                W_ANS.DOC_LINK AS ATT_EXT,
                W_ANS.SCORE AS WORK_SCORE
            ")
            ->from('ET_STUDENTWORK', 'ESW')
            ->leftJoin('ESW', 'T_TEACHERS', 'TT', 'ESW.TEACHER = TT.OID')
            ->leftJoin('TT', 'NPERSONS', 'N', 'TT.C_OID = N.OID')
            ->leftJoin('TT', 'ET_DOLTIMETABLE', 'ED', 'TT.DOLTIMETABLE = ED.OID')
            ->leftJoin('ESW', 'T_TKINDS', 'T', 'ESW.STUDYTYPE = T.OID')
            ->leftJoin(
                'ESW',
                sprintf('(%s)', $this->entityManager->getConnection()->createQueryBuilder()
                    ->select('ES.OID, ES.WORK, ES.FILE$DOC AS DOC_NAME, ES.NAME AS DOC_TITLE, ES.EXTLINK AS DOC_LINK, ROUND(DBMS_LOB.GETLENGTH(ES.DOC)/1024) AS DOC_SIZE, ESG.SCORE')
                    ->from('ET_SWATTACHMENT', 'ES')
                    ->leftJoin(
                        'ES',
                        sprintf('(%s)', $this->entityManager->getConnection()->createQueryBuilder()
                            ->select('ISWG.BALL AS SCORE, ISWG.WORK, ISWG.CONTINGENT')
                            ->from('ET_SWGRADES', 'ISWG')
                            ->innerJoin(
                                'ISWG',
                                sprintf('(%s)', $queryBuilder->getConnection()->createQueryBuilder()
                                    ->select('MAX(I2SWG.OID) AS MX')
                                    ->from('ET_SWGRADES', 'I2SWG')
                                    ->groupBy('I2SWG.CONTINGENT, I2SWG.WORK')
                                    ->getSQL()
                                ),
                                'MOID',
                                'MOID.MX = ISWG.OID'
                            )
                            ->getSQL()
                        ),
                        'ESG',
                        $queryBuilder->expr()->andX('ES.WORK = ESG.WORK', 'ESG.CONTINGENT = ES.CONTINGENT'))
                    ->where('ES.CONTINGENT = :CONT')
                    ->getSQL()
                ),
                'W_ANS',
                'W_ANS.WORK = ESW.OID'
            )
            ->where('ESW.CSEMESTER = :SEMESTER')
            ->andWhere('ESW.DISCIPLINE = :DISCIPLINE')
            ->andWhere('ESW.G = :GROUP');
        $result = $queryBuilder
            ->setParameter('SEMESTER', $semester)
            ->setParameter('DISCIPLINE', $discipline)
            ->setParameter('GROUP', $group)
            ->setParameter('CONT', $contingent)
            ->execute();

        $persons = [];
        $teachers = [];
        $works = [];

        while($workRow = $result->fetch())
        {
            if(!(key_exists($workId = $workRow['WORK_ID'], $works) && $work = $works[$workId])) {
                $work = new StudentWork();
                $work->setId($workId);
                $work->setWorkName($workRow['WORK_NAME']);
                $work->setWorkTheme($workRow['WORK_THEME']);
                $work->setWorkType($workRow['WORK_TYPE']);
                $work->setWorkMaxScore($workRow['SCORE']);

                if(!(key_exists($teacherPersonId = $workRow['TCH_POID'], $persons) && $teacherPerson = $persons[$teacherPersonId])) {
                    $teacherPerson = new Person();
                    $teacherPerson->setUoid($teacherPersonId);
                    if ($fname = $workRow['TCH_NAME']) {
                        $teacherPerson->setFname($this->stringConverter->capitalize($fname));
                    }
                    if($lname = $workRow['TCH_SURNAME']) {
                        $teacherPerson->setLname($this->stringConverter->capitalize($lname));
                    }
                    if($patronymic = $workRow['TCH_PTR']) {
                        $teacherPerson->setFname($patronymic);
                    }
                    $persons[$teacherPersonId] = $teacherPerson;
                }

                if(!(key_exists($teacherId = $workRow['TCH_ID'], $teachers) && $teacher = $teachers[$teacherId])) {
                    $teacher = new Teacher();
                    $teacher->setId($teacherId);
                    $teacher->setPosition($workRow['TCH_POST']);
                    $teacher->setPerson($teacherPerson);
                    $teachers[$teacherId] = $teacher;
                }

                $work->setTeacher($teacher);
                $works[$workId] = $work;
            }

            if(!$work->getAnswer() && (
                $workRow['WORK_SCORE'] ||
                $workRow['ATT_DOC_KB'] ||
                $workRow['ATT_FILENAME'] ||
                $workRow['ATT_TITLE'] ||
                $workRow['ATT_EXT'])
            ) {
                $answer = new WorkAnswer();
                $answer->setScore($workRow['WORK_SCORE']);
                $answer->setAttachments([]);
                $work->setAnswer($answer);
            }

            if($workRow['ATT_ID'] || $workRow['ATT_DOC_KB'] || $workRow['ATT_FILENAME'] || $workRow['ATT_TITLE'] || $workRow['ATT_EXT']) {
                $workAttachment = new WorkAttachment();
                $workAttachment->setAttachmentId($workRow['ATT_ID']);
                $workAttachment->setFileSize($workRow['ATT_DOC_KB']);
                $workAttachment->setFileName($workRow['ATT_FILENAME']);
                $workAttachment->setDisplayedName($workRow['ATT_TITLE']);
                $workAttachment->setExternalLink($workRow['ATT_EXT']);
                $work->getAnswer()->addAttachment($workAttachment);
            }
        }

        return $works;
    }
}