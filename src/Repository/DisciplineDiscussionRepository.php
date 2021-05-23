<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\DiscussionMessage;
use App\Model\DTO\ExternalLink;
use App\Model\DTO\Person;
use App\Service\StringConverter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class DisciplineDiscussionRepository extends AbstractRepository
{
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, StringConverter $stringConverter)
    {
        parent::__construct($entityManager);
        $this->stringConverter = $stringConverter;
    }

    /**
     * @param string $group
     * @param string $semester
     * @param string $discipline
     * @return int
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
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
            ->fetchAllAssociative();

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
     * @throws \Exception
     * @throws \Doctrine\DBAL\Driver\Exception
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
        while($messageRow = $response->fetchAssociative()) {
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
     * @param string $personId
     * @param string $semesterId
     * @param string $groupId
     * @param string $disciplineId
     * @return bool
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function getUserHasPermissionsToChat(
        string $personId,
        string $semesterId,
        string $groupId,
        string $disciplineId
    ): bool
    {
        $stmt = $this->getEntityManager()->getConnection()->prepare("
            SELECT COUNT(*) AS IS_MEM FROM (
                  SELECT DISTINCT TT2.C_OID AS MM_ID, TT2.NAME
                  FROM T_TIMETABLE TT
                           INNER JOIN T_TEACHERS TT2 ON TT.TEACHER = TT2.OID
                  WHERE TT.G = ?
                    AND TT.DISCIPLINE = ?
                    AND TT.CSEMESTER = ?
                  UNION ALL
                  SELECT EC.C_OID AS MM_ID, EC.NAME AS NAME
                  FROM ET_CONTINGENTS EC
                           INNER JOIN ET_GROUPS EG ON EC.G = EG.OID
                           INNER JOIN ET_RCONTINGENTS ETR ON EG.OID = ETR.G
                           INNER JOIN ET_CURRICULUMS ETCR ON ETR.PLAN = ETCR.OID
                           INNER JOIN ET_DSPLANS EDSP ON ETCR.OID = EDSP.EPLAN
                           INNER JOIN T_CONTSTATES TCN ON EC.ESTATE = TCN.OID
                  WHERE EG.OID = ?
                    AND EDSP.DISCIPLINE = ?
                    AND ETR.CSEMESTER = ?
                    AND EC.C_OID IS NOT NULL
                    AND TCN.NAME IN ('ИН', '2Г', 'УЧ', 'АК')
              ) MMBR WHERE MMBR.MM_ID = ?
        ");
        $stmt->bindValue(1, $groupId);
        $stmt->bindValue(2, $disciplineId);
        $stmt->bindValue(3, $semesterId);
        $stmt->bindValue(4, $groupId);
        $stmt->bindValue(5, $disciplineId);
        $stmt->bindValue(6, $semesterId);
        $stmt->bindValue(7, $personId);
        $result = $stmt->executeQuery()->fetchAllAssociative();

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
            ->setValue('OID', ':MSG_ID')
            ->setValue('AUTHOR', ':AUTHOR_ID')
            ->setValue('CREATED', "TO_DATE(:CURRENT_DATE, 'yyyy-mm-dd hh24:mi:ss')")
            ->setValue('CSEMESTER', ':SEMESTER_ID')
            ->setValue('DISCIPLINE', ':DISCIPLINE_ID')
            ->setValue('G', ':GROUP_ID')
            ->setValue('MSG', ':MESSAGE')
            ->setParameter('MSG_ID', $newOid)
            ->setParameter('AUTHOR_ID', $senderId)
            ->setParameter('CURRENT_DATE', (new \DateTime())->format('Y-m-d H:i:s'))
            ->setParameter('SEMESTER_ID', $semesterId)
            ->setParameter('DISCIPLINE_ID', $disciplineId)
            ->setParameter('GROUP_ID', $groupId)
            ->setParameter('MESSAGE', $message);
        if(count($attachments) > 0) {
            $queryBuilder
                ->setValue('DOC', ':DOCUMENT')
                ->setValue('FILE$DOC', ':DOC_NAME')
                ->setParameter('DOCUMENT', $attachments[0]->getFileContent())
                ->setParameter('DOC_NAME', $attachments[0]->getFilename());
        }

        if(count($links) > 0) {
            $queryBuilder
                ->setValue('EXTLINK', ':LINK_VALUE')
                ->setValue('TEXTLINK', ':LINK_TXT')
                ->setParameter('LINK_VALUE', $links[0]->getLinkContent())
                ->setParameter('LINK_TXT', $links[0]->getLinkText());
        }

        $queryBuilder->execute();

        return $newOid;
    }

    /**
     * @param string $messageId
     * @param string $userId
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
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
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
    }

    /**
     * @param string $messageId
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function isMessageExists(string $messageId) : bool
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('COUNT(*) AS CNT')
            ->from('ET_MSG_LK', 'EM')
            ->where('EM.OID = :MESSAGE_ID')
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
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
            ->setParameter('FILE_DATA', $file->getFileContent(), 'blob')
            ->setParameter('FILE_NAME', $file->getFilename())
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute();
    }

    /**
     * @param string $messageId
     * @return BinaryFile
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getMessageAttachment(string $messageId) {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('EM.DOC, EM.FILE$DOC AS FILE_NAME')
            ->from('ET_MSG_LK', 'EM')
            ->where('EM.OID = :MESSAGE_ID')
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute()
            ->fetchAssociative();

        if(count($result) !== 1) {
            throw new NotFoundException('DisciplineDiscussionMessage');
        }

        $binaryFile = new BinaryFile();
        $binaryFile->setFilename($result[0]['FILE_NAME']);
        $binaryFile->setFileContent($result[0]['DOC']);

        return $binaryFile;
    }
}