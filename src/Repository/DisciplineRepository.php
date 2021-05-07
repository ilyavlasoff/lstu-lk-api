<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\Mapping\Attachment;
use App\Model\Mapping\BinaryFile;
use App\Model\Mapping\Chair;
use App\Model\Mapping\Discipline;
use App\Model\Mapping\DiscussionMessage;
use App\Model\Mapping\ExternalLink;
use App\Model\Mapping\Faculty;
use App\Model\Mapping\Person;
use App\Model\Mapping\StudentWork;
use App\Model\Mapping\Teacher;
use App\Model\Mapping\TeachingMaterial;
use App\Model\Mapping\TimetableItem;
use App\Model\Mapping\WorkAnswer;
use App\Model\Request\SendingDiscussionMessage;
use App\Service\StringConverter;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Json;

class DisciplineRepository extends AbstractRepository
{
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        parent::__construct($entityManager);
        $this->stringConverter = $stringConverter;
    }

    /**
     * @param string $discipline
     * @return bool
     * @throws Exception
     */
    public function isDisciplineExists(string $discipline): bool {
        $dis = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('EDIS.OID')
            ->from('ET_DISCIPLINES', 'EDIS')
            ->where('EDIS.OID = :DISCIPLINE')
            ->setParameter('DISCIPLINE', $discipline)
            ->execute()
            ->fetchAll(FetchMode::COLUMN);

        return count($dis) === 1;
    }

    /**
     * @param string $discipline
     * @return \App\Model\Mapping\Discipline
     * @throws Exception
     */
    public function getDiscipline(string $discipline): Discipline
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

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
            ->fetchAll();

        if(count($disciplineRows) !== 1) {
            throw new NotFoundException('Discipline');
        }

        $disciplineData = $disciplineRows[0];

        $discipline = new Discipline();
        $discipline->setId($disciplineData['DIS_ID']);
        $discipline->setName($disciplineData['DIS_NAME']);
        $discipline->setCategory($disciplineData['DISCAT_NAME']);

        $chair = new Chair();
        $chair->setId($disciplineData['CH_ID']);
        $chair->setChairName($disciplineData['CH_NAME']);

        $faculty = new Faculty();
        $faculty->setId($disciplineData['FAC_ID']);
        $faculty->setFacName($disciplineData['FAC_NAME']);
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
     */
    public function getTeachersByDiscipline(string $discipline, string $group, string $semester): array
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $result = $queryBuilder
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
            ->setParameter('DISCIPLINE', $discipline)
            ->execute();

        $disciplineTeachers = [];
        while($teacherRow = $result->fetch()) {
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
     * @param string $group
     * @param string $semester
     * @param string $discipline
     * @return int
     * @throws Exception
     */
    public function getDisciplineChatMessagesCount(string $group, string $semester, string $discipline): int
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
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

    /**
     * @param string $semester
     * @param string $discipline
     * @param string $group
     * @param int $offset
     * @param int $limit
     * @return array
     * @throws Exception
     */
    public function getDisciplineChatMessages(
        string $semester,
        string $discipline,
        string $group,
        int $offset,
        int $limit
    ): array {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
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
                $attachment = new Attachment();
                $attachment->setAttachmentName($attachmentName);
                $attachment->setAttachmentSize($attachmentSize);
                $message->setAttachments([$attachment]);
            }

            if(($externalLink = $messageRow['LINK_TEXT']) && ($externalLocation = $messageRow['LOCATION'])) {
                $extLink = new ExternalLink();
                $extLink->setLinkContent($externalLocation);
                $extLink->setLinkText($externalLink);
                $message->setExternalLinks([$extLink]);
            }

            $discussionMessages[] = $message;
        }

        return $discussionMessages;
    }

    /**
     * @param string $semester
     * @param string $discipline
     * @param string $group
     * @param string $contingent
     * @return array
     * @throws Exception
     */
    public function getStudentWorksList(
        string $semester,
        string $discipline,
        string $group,
        string $contingent
    ): array
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
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
                sprintf('(%s)', $this->getEntityManager()->getConnection()->createQueryBuilder()
                    ->select('ES.OID, ES.WORK, ES.FILE$DOC AS DOC_NAME, ES.NAME AS DOC_TITLE, ES.EXTLINK AS DOC_LINK, ROUND(DBMS_LOB.GETLENGTH(ES.DOC)/1024) AS DOC_SIZE, ESG.SCORE')
                    ->from('ET_SWATTACHMENT', 'ES')
                    ->leftJoin(
                        'ES',
                        sprintf('(%s)', $this->getEntityManager()->getConnection()->createQueryBuilder()
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

            if($workRow['ATT_DOC_KB'] || $workRow['ATT_FILENAME']) {
                $workAttachment = new Attachment();
                $workAttachment->setAttachmentSize($workRow['ATT_DOC_KB']);
                $workAttachment->setAttachmentName($workRow['ATT_FILENAME']);
                $work->getAnswer()->addAttachment($workAttachment);
            }

            if($workRow['ATT_TITLE'] || $workRow['ATT_EXT']) {
                $externalLink = new ExternalLink();
                $externalLink->setLinkText($workRow['ATT_TITLE']);
                $externalLink->setLinkContent($workRow['ATT_EXT']);
                $work->getAnswer()->addExtLink($externalLink);
            }
        }

        return $works;
    }

    /**
     * @param string $groupId
     * @param string $semesterId
     * @return array
     * @throws Exception
     */
    public function getDisciplinesBySemester(string $groupId, string $semesterId): array
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        //$result = $queryBuilder->select('EDIS.OID as DISCIPLINE_ID, EDIS.NAME AS DISCIPLINE_NAME, ECH.VALUE AS CHAIR_NAME')
        $result = $queryBuilder->select('EDIS.OID as DISCIPLINE_ID, EDIS.NAME AS DISCIPLINE_NAME')
            ->from('ET_RCONTINGENTS', 'ER')
            ->innerJoin('ER', 'ET_GROUPS', 'EG', 'ER.G = EG.OID')
            ->innerJoin('ER', 'ET_CSEMESTERS', 'ECSEM', 'ER.CSEMESTER = ECSEM.OID')
            ->innerJoin('ER', 'ET_CURRICULUMS', 'EC', 'ER.PLAN = EC.OID')
            ->innerJoin('EC', 'ET_DSPLANS', 'ED', $queryBuilder->expr()->andX('EC.OID = ED.EPLAN', 'ED.SEMESTER = ER.SEMESTER'))
            ->innerJoin('ED', 'ET_DISCIPLINES', 'EDIS', 'ED.DISCIPLINE = EDIS.OID')
            //->leftJoin('ED', 'ET_CHAIRS', 'ECH', 'ED.CHAIR = ECH.OID')
            ->where('EG.OID = :GROUPID')
            ->andWhere('ECSEM.OID = :SEMESTERID')
            ->setParameter('GROUPID', $groupId)
            ->setParameter('SEMESTERID', $semesterId)
            ->execute();

        $subjectList = [];
        while($subject = $result->fetch())
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

    /**
     * @param string $disciplineId
     * @param string $educationId
     * @param string $semesterId
     * @return array
     * @throws Exception
     */
    public function getDisciplineTeachingMaterials(string $disciplineId, string $educationId, string $semesterId, ?bool $withFiles = false)
    {
        $subQ = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('RC.PLAN')
            ->from('ET_RCONTINGENTS', 'RC')
            ->innerJoin('RC', 'ET_CONTINGENTS', 'EC2', 'EC2.G = RC.G')
            ->where('EC2.OID = :EDUCATION AND RC.CSEMESTER = :SEMESTER')
            ->getSQL();

        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('
                ETM.OID AS MATERIAL_ID, 
                ETM.NAME AS MATERIAL_NAME,
                ETMC.NAME AS MATERIAL_TYPE,
                FILE$DOC AS ATT_NAME, 
                ROUND(DBMS_LOB.GETLENGTH(ETM.DOC)/1024) AS DOC_KB,
                ETM.EXTLINK AS FILE_LINK
            ');
        if($withFiles) {
            $queryBuilder->addSelect('ETM.DOC');
        }
        $queryBuilder
            ->from('ET_TEACHINGMATERIALS', 'ETM')
            ->leftJoin('ETM', 'ET_GROUPS', 'EG', 'ETM.G = EG.OID')
            ->leftJoin('EG', 'ET_CONTINGENTS', 'EC', 'EG.OID = EC.G')
            ->leftJoin('ETM', 'ET_MATCATEGORIES', 'ETMC', 'ETM.MATCATEGORY = ETMC.OID')
            ->where('ETM.DISCIPLINE = :DISCIPLINE')
            ->andWhere($queryBuilder->expr()->or('EC.OID = :EDUCATION', 'ETM.G IS NULL'))
            ->andWhere($queryBuilder->expr()->or("ETM.CURRICULUM = ($subQ)", 'ETM.CURRICULUM IS NULL'))
            ->setParameter('DISCIPLINE', $disciplineId)
            ->setParameter('EDUCATION', $educationId)
            ->setParameter('SEMESTER', $semesterId);

        $result = $queryBuilder->execute();

        $teachingMaterials = [];

        while($materialRow = $result->fetch()) {
            $teachingMaterial = new TeachingMaterial();
            $teachingMaterial->setId($materialRow['MATERIAL_ID']);
            $teachingMaterial->setMaterialName($materialRow['MATERIAL_NAME']);
            $teachingMaterial->setMaterialType($materialRow['MATERIAL_TYPE']);

            if($fileSize = $materialRow['DOC_KB']) {
                $attachment = new Attachment();
                $attachment->setAttachmentSize($fileSize);
                $attachment->setAttachmentName($materialRow['ATT_NAME']);
                $teachingMaterial->setAttachment($attachment);
                if($withFiles && $documentData = $materialRow['DOC']) {
                    $attachment->setB64attachment(base64_encode($documentData));
                }
            }

            if($link = $materialRow['FILE_LINK']) {
                $externalLink = new ExternalLink();
                $externalLink->setLinkContent($link);
                $externalLink->setLinkText($materialRow['ATT_NAME']);
                $teachingMaterial->setExternalLink($externalLink);
            }

            $teachingMaterials[] = $teachingMaterial;
        }

        return $teachingMaterials;
    }

    /**
     * @param string $personId
     * @param string $semesterId
     * @param string $groupId
     * @param string $disciplineId
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function isUserAllowedToSendMessageToDisciplineChat(string $personId, string $semesterId, string $groupId, string $disciplineId): bool
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare("
            SELECT COUNT(*) AS IS_MEM FROM (
                  SELECT DISTINCT TT2.C_OID AS MM_ID, TT2.NAME
                  FROM T_TIMETABLE TT
                           INNER JOIN T_TEACHERS TT2 ON TT.TEACHER = TT2.OID
                  WHERE TT.G = :GROUP
                    AND TT.DISCIPLINE = :DISCIPLINE
                    AND TT.CSEMESTER = :SEMESTER
                  UNION ALL
                  SELECT EC.C_OID AS MM_ID, EC.NAME AS NAME
                  FROM ET_CONTINGENTS EC
                           INNER JOIN ET_GROUPS EG ON EC.G = EG.OID
                           INNER JOIN ET_RCONTINGENTS ETR ON EG.OID = ETR.G
                           INNER JOIN ET_CURRICULUMS ETCR ON ETR.PLAN = ETCR.OID
                           INNER JOIN ET_DSPLANS EDSP ON ETCR.OID = EDSP.EPLAN
                           INNER JOIN T_CONTSTATES TCN ON EC.ESTATE = TCN.OID
                  WHERE EG.OID = :GROUP
                    AND EDSP.DISCIPLINE = :DISCIPLINE
                    AND ETR.CSEMESTER = :SEMESTER
                    AND EC.C_OID IS NOT NULL
                    AND TCN.NAME IN ('ИН', '2Г', 'УЧ', ' АК')
              ) MMBR WHERE MMBR.MM_ID = :PERSON
        ");
            $stmt->bindParam('GROUP', $groupId);
            $stmt->bindParam('DISCIPLINE', $disciplineId);
            $stmt->bindParam('SEMESTER', $semesterId);
            $stmt->bindParam('PERSON', $personId);
            $stmt->execute();
            $result = $stmt->fetchAll();

        return (bool)$result[0]['IS_MEM'];
    }

    /**
     * @param string $message
     * @param BinaryFile[] $attachments
     * @param ExternalLink[] $links
     * @param string $senderId
     * @param string $semesterId
     * @param string $disciplineId
     * @param string $groupId
     * @return string
     * @throws Exception
     */
    public function addNewDisciplineDiscussionMessage(
        string $message,
        array $attachments,
        array $links,
        string $senderId,
        string $semesterId,
        string $disciplineId,
        string $groupId
    ): string {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $newOid = $this->getNewOid();
        $queryBuilder
            ->insert('ET_MSG_LK')
            ->setParameter('OID', $newOid)
            ->setParameter('AUTHOR', $senderId)
            ->setParameter('CREATED', new \DateTime())
            ->setParameter('CSEMESTER', $semesterId)
            ->setParameter('DISCIPLINE', $disciplineId)
            ->setParameter('G', $groupId)
            ->setParameter('MSG', $message);
        if(count($attachments) > 0) {
            $queryBuilder
                ->setParameter('DOC', $attachments[0]->getFileContent())
                ->setParameter('FILE$DOC', $attachments[0]->getFilename());
        }

        if(count($links) > 0) {
            $queryBuilder
                ->setParameter('EXTLINK', $links[0]->getLinkContent())
                ->setParameter('TEXTLINK', $links[0]->getLinkText());
        }

        $queryBuilder->execute();

        return $newOid;
    }

    /**
     * @param string $messageId
     * @param BinaryFile $file
     * @throws Exception
     */
    public function addAttachmentToMessage(string $messageId, BinaryFile $file)
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder
            ->update('ET_MSG_LK', 'EM')
            ->set('EM.DOC', ':FILE_DATA')
            ->set('EM.FILE$DOC', ':FILE_NAME')
            ->where('EM.OID = :MESSAGE_ID')
            ->setParameter('FILE_DATA', $file->getFileContent())
            ->setParameter('FILE_NAME', $file->getFilename())
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute();
    }

    /**
     * @param string $messageId
     * @return bool
     * @throws Exception
     */
    public function isMessageExists(string $messageId) : bool
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('COUNT(*) AS CNT')
            ->from('ET_MSG_LK', 'EM')
            ->where('EM.OID = :MESSAGE_ID')
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute()
            ->fetchAll();

        return $result[0]['CNT'] === 1;
    }

    /**
     * @param string $messageId
     * @param string $userId
     * @return bool
     * @throws Exception
     */
    public function isMessageBelongsToUser(string $messageId, string $userId): bool
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('COUNT(*) AS CNT')
            ->from('ET_MSG_LK', 'EM')
            ->where('EM.OID = :MESSAGE_ID')
            ->where('EM.AUTHOR = :AUTHOR')
            ->setParameter('MESSAGE_ID', $messageId)
            ->setParameter('AUTHOR', $userId)
            ->execute()
            ->fetchAll();

        return $result[0]['CNT'] === 1;
    }

    /**
     * @param string $materialId
     * @return NotFoundException|mixed
     * @throws Exception
     */
    public function getTeachingMaterialsAttachment(string $materialId)
    {
        /*$result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('ETM.DOC')
            ->from('ET_TEACHINGMATERIALS', 'ETM')
            ->where('ETM.OID = :MATERIAL_ID')
            ->setParameter('MATERIAL_ID', $materialId)
            ->execute()
            ->fetchAll();*/
        $stmt = $this->getEntityManager()->getConnection()->prepare('
            SELECT ETM.DOC FROM ET_TEACHINGMATERIALS ETM WHERE ETM.OID = :MATERIAL_ID
        ');
        $stmt->bindValue('MATERIAL_ID', $materialId);
        $stmt->execute();
        $result = $stmt->fetch();

        if(count($result) !== 1) {
            return new NotFoundException('Material');
        }

        return $result[0]['DOC'];
    }
}