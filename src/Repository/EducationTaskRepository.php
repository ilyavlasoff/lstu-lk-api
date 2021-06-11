<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\ExternalLink;
use App\Model\DTO\Person;
use App\Model\DTO\StudentWork;
use App\Model\DTO\Teacher;
use App\Model\DTO\WorkAnswer;
use App\Model\DTO\WorkAnswerAttachment;
use App\Service\StringConverter;
use Doctrine\DBAL\Exception;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

class EducationTaskRepository extends AbstractRepository
{
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, DocumentManager $documentManager, StringConverter $stringConverter)
    {
        parent::__construct($entityManager, $documentManager);
        $this->stringConverter = $stringConverter;
    }

    /**
     * @param string $educationId
     * @param string $workId
     * @param string $answerName
     * @param BinaryFile[] $attachments
     * @param ExternalLink[] $externalLinks
     * @return String
     * @throws Exception
     */
    public function addEducationTaskAnswer(
        string $educationId,
        string $workId,
        ?string $answerName,
        ?array $attachments,
        ?array $externalLinks
    ): string
    {
        $newOid = $this->getNewOid();

        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder
            ->insert('ET_SWATTACHMENT')
            ->setValue('OID', ':ID')
            ->setValue('CONTINGENT', ':EDUCATION_ID')
            ->setValue('NAME', ':NAME')
            ->setValue('WORK', ':WORK')
            ->setParameter('ID', $newOid)
            ->setParameter('EDUCATION_ID', $educationId)
            ->setParameter('NAME', $answerName)
            ->setParameter('WORK', $workId);

        if($attachments) {
            $queryBuilder
                ->setValue('DOC', ':DOC_CONTENT')
                ->setValue('FILE$DOC', ':DOC_NAME')
                ->setParameter('DOC_CONTENT', $attachments[0]->getFileContent())
                ->setParameter('DOC_NAME', $attachments[0]->getFilename());
        };
        if($externalLinks) {
            $queryBuilder
                ->setValue('EXTLINK', ':LINK')
                ->setParameter('LINK', $externalLinks[0]->getLinkContent());
        }
        $queryBuilder->execute();

        return $newOid;
    }

    /**
     * @param BinaryFile $file
     * @param string $answerId
     * @throws Exception
     */
    public function addAnswerDocument(BinaryFile $file, string $answerId) {
        $queryBuilder = $this->getConnection()->createQueryBuilder()
            ->update('ET_SWATTACHMENT')
            ->set('DOC', ':DOC_CONTENT')
            ->set('FILE$DOC', ':DOC_NAME')
            ->where('OID = :ANSWER_ID')
            ->setParameter('DOC_CONTENT', $file->getFileContent(), 'blob')
            ->setParameter('DOC_NAME', $file->getFilename())
            ->setParameter('ANSWER_ID', $answerId);

        $queryBuilder->execute();
    }

    /**
     * @param string $userId
     * @param string $answerId
     * @return mixed
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getUserIsSenderForAttachment(string $userId, string $answerId)
    {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('COUNT(*) AS CNT')
            ->from('ET_SWATTACHMENT', 'SWAT')
            ->innerJoin('SWAT', 'ET_CONTINGENTS', 'ETC', 'ETC.OID = SWAT.CONTINGENT')
            ->where('SWAT.OID = :ANSWER_ID')
            ->andWhere('ETC.C_OID = :USER_ID')
            ->setParameter('ANSWER_ID', $answerId)
            ->setParameter('USER_ID', $userId)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
    }

    /**
     * @param string $answerId
     * @return BinaryFile
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getEducationTaskAnswer(string $answerId): BinaryFile
    {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('SWAT.DOC, SWAT.FILE$DOC AS ATT_NAME')
            ->from('ET_SWATTACHMENT', 'SWAT')
            ->where('SWAT.OID = :ANSWER_ID')
            ->setParameter('ANSWER_ID', $answerId)
            ->execute()
            ->fetchAllAssociative();

        if(count($result) !== 1) {
            throw new NotFoundException('Answer');
        }

        $binaryFile = new BinaryFile();
        $binaryFile->setFileContent($result[0]['DOC']);
        $binaryFile->setFilename($result[0]['ATT_NAME']);

        return $binaryFile;
    }

    /**
     * @param string $semester
     * @param string $discipline
     * @param string $group
     * @param string $contingent
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getEducationTasksList(
        string $semester,
        string $discipline,
        string $group,
        string $contingent
    ): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
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
                sprintf('(%s)', $this->getConnection()->createQueryBuilder()
                    ->select('
                        ES.OID, 
                        ES.WORK, 
                        ES.FILE$DOC AS DOC_NAME, 
                        ES.NAME AS DOC_TITLE, 
                        ES.EXTLINK AS DOC_LINK, 
                        ROUND(DBMS_LOB.GETLENGTH(ES.DOC)/1024) AS DOC_SIZE,
                        ESG.SCORE')
                    ->from('ET_SWATTACHMENT', 'ES')
                    ->leftJoin(
                        'ES',
                        sprintf('(%s)', $this->getConnection()->createQueryBuilder()
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

        while($workRow = $result->fetchAssociative())
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
                        $teacherPerson->setPatronymic($this->stringConverter->capitalize($patronymic));
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
                $answer->setAnswerAttachments([]);
                $work->setAnswer($answer);
            }

            $attachmentExists = $workRow['ATT_DOC_KB'] && $workRow['ATT_FILENAME'];
            $linkExists = $workRow['ATT_TITLE'] && $workRow['ATT_EXT'];
            if($attachmentExists || $linkExists) {
                $answerAttachment = new WorkAnswerAttachment();
                $answerAttachment->setName($workRow['ATT_TITLE']);
                $answerAttachment->setId($workRow['ATT_ID']);

                if($attachmentExists) {
                    $workAttachment = new Attachment();
                    $workAttachment->setAttachmentSize($workRow['ATT_DOC_KB']);
                    $workAttachment->setAttachmentName($workRow['ATT_FILENAME']);
                    $answerAttachment->setAttachments([$workAttachment]);
                }

                if($linkExists) {
                    $externalLink = new ExternalLink();
                    $externalLink->setLinkContent($workRow['ATT_EXT']);
                    $answerAttachment->setExtLinks([$externalLink]);
                }

                $work->getAnswer()->addAnswerAttachments($answerAttachment);
            }
        }

        return array_values($works);
    }
}