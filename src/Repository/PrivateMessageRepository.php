<?php

namespace App\Repository;

use App\Exception\RestException;
use App\Exception\DataAccessException;
use App\Exception\DuplicateValueException;
use App\Exception\NotFoundException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\Dialog;
use App\Model\DTO\ExternalLink;
use App\Model\DTO\Person;
use App\Model\DTO\PrivateMessage;
use App\Service\StringConverter;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManagerInterface;

class PrivateMessageRepository extends AbstractRepository
{
    private $stringConverter;

    public function __construct(EntityManagerInterface $entityManager, DocumentManager $documentManager, StringConverter $converter)
    {
        parent::__construct($entityManager, $documentManager);
        $this->stringConverter = $converter;
    }

    /**
     * @param string $person
     * @param string $companion
     * @return array
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getExistingDialogId(string $person, string $companion): array
    {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('EDCL.OID AS DIALOG_ID')
            ->from('ET_DIALOG_CHAT_LK', 'EDCL')
            ->where("(EDCL.MEMBER1 = :FIRST AND EDCL.MEMBER2 = :SECOND) 
                OR (EDCL.MEMBER2 = :FIRST AND EDCL.MEMBER1 = :SECOND)")
            ->setParameter('FIRST', $person)
            ->setParameter('SECOND', $companion)
            ->execute()
            ->fetchAllAssociative();

        $dialogs = [];
        foreach ($result as $dialog) {
            $dialogs[] = $dialog['DIALOG_ID'];
        }

        return $dialogs;
    }

    /**
     * @param string $dialog
     * @return bool
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getDialogExists(string $dialog): bool
    {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('COUNT(EDCL.OID) AS CNT')
            ->from('ET_DIALOG_CHAT_LK', 'EDCL')
            ->where('EDCL.OID = :DIALOG')
            ->setParameter('DIALOG', $dialog)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
    }

    /**
     * @param string $messageId
     * @return bool
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getMessageExists(string $messageId): bool
    {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('COUNT(*) AS CNT')
            ->from('ET_MSG_CHAT_LK', 'EMSG')
            ->where('EMSG.OID = :MESSAGE_ID')
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'] == 1;
    }

    /**
     * @param string $dialogId
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDialogParticipants(string $dialogId): array
    {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('EDL.MEMBER1, EDL.MEMBER2')
            ->from('ET_DIALOG_CHAT_LK', 'EDL')
            ->where('EDL.OID = :DIALOG_ID')
            ->setParameter('DIALOG_ID', $dialogId)
            ->execute()
            ->fetchAllAssociative();

        if(count($result) !== 1) {
            throw new NotFoundException('Dialog');
        }

        return [
            $result[0]['MEMBER1'],
            $result[0]['MEMBER2']
        ];
    }

    /**
     * @param string $messageId
     * @return mixed
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getMessageSender(string $messageId) {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('EMSG.AUTHOR')
            ->from('ET_MSG_CHAT_LK', 'EMSG')
            ->where('EMSG.OID = :MESSAGE_ID')
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute()
            ->fetchAllAssociative();

        if(count($result) !== 1) {
            throw new NotFoundException('Message');
        }

        return $result[0]['AUTHOR'];
    }

    /**
     * @param string $messageId
     * @return mixed
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getDialogByMessage(string $messageId) {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('EMSG.DIALOG')
            ->from('ET_MSG_CHAT_LK', 'EMSG')
            ->where('EMSG.OID = :MESSAGE_ID')
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute()
            ->fetchAllAssociative();

        if(count($result) !== 1) {
            throw new NotFoundException('Message');
        }

        return $result[0]['DIALOG'];
    }

    /**
     * @param string $person
     * @param string $companion
     * @param bool $errorOnExists
     * @return string
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function startDialog(string $person, string $companion, bool $errorOnExists = false): string
    {
        if(count($existingDialogs = $this->getExistingDialogId($person, $companion)) > 0) {
            if(!$errorOnExists) {
                return $existingDialogs[0];
            }
            throw new DuplicateValueException('Dialog');
        }

        $conn = $this->getConnection();
        $queryBuilder = $conn->createQueryBuilder();

        $newOid = $this->getNewOid();

        $queryBuilder
            ->insert('ET_DIALOG_CHAT_LK')
            ->setValue('OID', ':OID')
            ->setValue('MEMBER1', ':PERSON')
            ->setValue('MEMBER2', ':COMPANION')
            ->setParameter('PERSON', $person)
            ->setParameter('COMPANION', $companion)
            ->setParameter('OID', $newOid)
            ->execute();

        return $newOid;
    }

    /**
     * @param string|null $person
     * @return PrivateMessage[]
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     * @throws \Exception
     */
    public function getUnreadMessages(?string $person): array
    {
        $firstStartingQb = $this->getConnection()->createQueryBuilder();
        $fsQr = $firstStartingQb
            ->select('SELECT EDCL.OID AS CHAT_ID,
                    EMCL.OID AS MSG_ID,
                    EDCL.MEMBER1 AS RECEIVER_ID,
                    NP.OID AS SENDER_ID,
                    NP.FNAME AS SENDER_FNAME,
                    NP.FAMILY AS SENDER_LNAME,
                    NP.MNAME AS SENDER_PTR,
                    EMCL.CREATED,
                    EMCL.NAME,
                    CASE WHEN DOC IS NOT NULL THEN round(DBMS_LOB.getlength(FILE$DOC)/1024) END AS DOC_SIZE')
            ->from('ET_MSG_CHAT_LK', 'EMCL')
            ->innerJoin('EMCL', 'ET_DIALOG_CHAT_LK', 'EDCL', 'EMCL.DIALOG = EDCL.OID')
            ->innerJoin('EDCL', 'NPERSONS', 'NP', 'EDCL.MEMBER2 = NP.OID')
            ->where('EMCL.AUTHOR <> EDCL.MEMBER1')
            ->andWhere($firstStartingQb->expr()->or('(EDCL.LAST_MSG_1 IS NULL',
                "TO_NUMBER(REGEXP_SUBSTR(EDCL.LAST_MSG_1, '[^\d:]\d+$')) < EMCL.NUM)"))
            ->getSQL();

        $secondStartingQb = $this->getConnection()->createQueryBuilder();
        $ssQr = $secondStartingQb
            ->select('SELECT EDCL.OID AS CHAT_ID,
                    EMCL.OID AS MSG_ID,
                    EDCL.MEMBER2 AS RECEIVER_ID,
                    NP.OID AS SENDER_ID,
                    NP.FNAME AS SENDER_FNAME,
                    NP.FAMILY AS SENDER_LNAME,
                    NP.MNAME AS SENDER_PTR,
                    EMCL.CREATED,
                    EMCL.NAME,
                    CASE WHEN DOC IS NOT NULL THEN round(DBMS_LOB.getlength(FILE$DOC)/1024) END AS DOC_SIZE')
            ->from('ET_MSG_CHAT_LK', 'EMCL')
            ->innerJoin('EMCL', 'ET_DIALOG_CHAT_LK', 'EDCL', 'EMCL.DIALOG = EDCL.OID')
            ->innerJoin('EDCL', 'NPERSONS', 'NP', 'EDCL.MEMBER1 = NP.OID')
            ->where('EMCL.AUTHOR <> EDCL.MEMBER2')
            ->andWhere($secondStartingQb->expr()->or('(EDCL.LAST_MSG_2 IS NULL',
                "TO_NUMBER(REGEXP_SUBSTR(EDCL.LAST_MSG_2, '[^\d:]\d+$')) < EMCL.NUM)"))
            ->getSQL();

        $unionQuery = "$fsQr UNION $ssQr";

        $qb = $this->getConnection()->createQueryBuilder()
            ->select('UQ.CHAT_ID, UQ.MSG_ID, UQ.RECEIVER_ID, UQ.SENDER_ID, UQ.SENDER_FNAME, 
                UQ.SENDER_LNAME, UQ.SENDER_PTR, UQ.CREATED, UQ.NAME AS M_TEXT, UQ.DOC_SIZE')
            ->from($unionQuery, 'UQ');

        if($person) {
            $qb->where('UQ.RECEIVER_ID = :PERSONID');
        }

        $result = $qb->execute();

        $messagesList = [];
        while ($messageRow = $result->fetchAssociative()) {
            $sender = new Person();
            $sender->setUoid($messageRow['SENDER_ID']);
            $sender->setFname($this->stringConverter->capitalize($messageRow['SENDER_FNAME']));
            $sender->setLname($this->stringConverter->capitalize($messageRow['SENDER_LNAME']));
            $sender->setPatronymic($this->stringConverter->capitalize($messageRow['SENDER_PTR']));

            $message = new PrivateMessage();
            if($sender->getUoid() === $person) {
                $message->setMeSender(true);
            } else {
                $message->setSender($sender);
            }

            $message->setId($messageRow['MSG_ID']);
            $message->setChat($messageRow['CHAT_ID']);
            $message->setSendTime(new \DateTime($messageRow['CREATED']));
            $message->setMessageText($messageRow['M_TEXT']);
            $message->setIsRead(false);

            $messagesList[] = $message;
        }

        return $messagesList;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getDialogCount(string $user): int
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $result = $queryBuilder
            ->select('COUNT(*) AS CNT')
            ->from('ET_DIALOG_CHAT_LK', 'EDCL')
            ->where($queryBuilder->expr()->or('EDCL.MEMBER1 = :PERSON', 'EDCL.MEMBER2 = :PERSON'))
            ->setParameter('PERSON', $user)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'];
    }

    /**
     * @param string $person
     * @param string|null $offset
     * @param string|null $count
     * @param string|null $dialogId
     * @return Dialog[]
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getUserDialogs(string $person, ?string $offset = null, ?string $count = null, ?string $dialogId = null): array
    {
        $subQ1 = $this->getConnection()->createQueryBuilder()
            ->select("EDCL.OID AS DIALOG, EDCL.MEMBER1 AS PERSON, EDCL.MEMBER2 AS COMPANION,
                (SELECT COUNT(EMCL.OID) FROM ET_MSG_CHAT_LK EMCL WHERE EMCL.DIALOG = EDCL.OID 
                    AND (EMCL.NUM > TO_NUMBER(REGEXP_SUBSTR(EDCL.LAST_MSG_1, '[^\d:]\d+$')) OR EDCL.LAST_MSG_1 IS NULL)) AS UNREAD_COUNT,
                (SELECT COUNT(EMCL2.OID) FROM ET_MSG_CHAT_LK EMCL2 WHERE EMCL2.DIALOG = EDCL.OID 
                    AND (EMCL2.NUM > TO_NUMBER(REGEXP_SUBSTR(EDCL.LAST_MSG_2, '[^\d:]\d+$')) OR EDCL.LAST_MSG_2 IS NULL)) AS COMPANION_UNREAD")
            ->from('ET_DIALOG_CHAT_LK', 'EDCL')
            ->getSQL();

        $subQ2 = $this->getConnection()->createQueryBuilder()
            ->select("EDCL2.OID AS DIALOG, EDCL2.MEMBER2 AS PERSON, EDCL2.MEMBER1 AS COMPANION,
            (SELECT COUNT(EMCL3.OID) FROM ET_MSG_CHAT_LK EMCL3 WHERE EMCL3.DIALOG = EDCL2.OID 
                AND (EMCL3.NUM > TO_NUMBER(REGEXP_SUBSTR(EDCL2.LAST_MSG_2, '[^\d:]\d+$')) OR EDCL2.LAST_MSG_2 IS NULL)) AS UNREAD_COUNT,
            (SELECT COUNT(EMCL4.OID) FROM ET_MSG_CHAT_LK EMCL4 WHERE EMCL4.DIALOG = EDCL2.OID 
                AND (EMCL4.NUM > TO_NUMBER(REGEXP_SUBSTR(EDCL2.LAST_MSG_1, '[^\d:]\d+$')) OR EDCL2.LAST_MSG_1 IS NULL)) AS COMPANION_UNREAD")
            ->from('ET_DIALOG_CHAT_LK', 'EDCL2')
            ->getSQL();

        $queryBuilder = $this->getConnection()->createQueryBuilder()
            ->select('DM.DIALOG, NP.OID AS COMPANION_ID, NP.FNAME AS COMPANION_FNAME, NP.FAMILY AS COMPANION_LNAME, 
                NP.MNAME AS COMPANION_PTR, 
                EMLK.NAME AS LAST_MGS, 
                EMLK.CREATED AS SEND_TIME, 
                EMLK.OID AS M_ID, 
                EMLK.AUTHOR AS AUTHOR,
                round(DBMS_LOB.getlength(EMLK.DOC) / 1024) AS ATT_SIZE, 
                EMLK.FILE$DOC AS DOCNAME,
                EMLK.LINK AS LINK, 
                EMLK.TEXTLINK AS LINK_TEXT, 
                DM.UNREAD_COUNT, 
                DM.COMPANION_UNREAD,
                SNP.OID AS LMS_ID, SNP.FNAME AS LMS_FNAME, 
                SNP.FAMILY AS LMS_LNAME, 
                SNP.MNAME AS LMS_PTR'
            )
            ->from("($subQ1 UNION ALL $subQ2)", 'DM')
            ->innerJoin('DM', 'NPERSONS', 'NP', 'DM.COMPANION = NP.OID')
            ->leftJoin('DM', 'ET_MSG_CHAT_LK', 'EMLK', 'EMLK.DIALOG = DM.DIALOG')
            ->leftJoin('EMLK', 'NPERSONS', 'SNP', 'EMLK.AUTHOR = SNP.OID')
            ->where('(EMLK.NUM = (SELECT MAX(NUM) FROM ET_MSG_CHAT_LK WHERE ET_MSG_CHAT_LK.DIALOG = DM.DIALOG) OR EMLK.NUM IS NULL)')
            ->andWhere('DM.PERSON = :PERSON')
            ->orderBy('EMLK.CREATED', 'DESC');

        if($dialogId) {
            $queryBuilder
                ->andWhere('DM.DIALOG = :DIALOG')
                ->setParameter('DIALOG', $dialogId);
        }

        if($offset && $count) {
            $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($count);
        }

        $result = $queryBuilder
            ->setParameter('PERSON', $person)
            ->execute();

        $loadedDialogs = [];
        while ($dialogRow = $result->fetchAssociative()) {
            $companion = new Person();
            $companion->setUoid($dialogRow['COMPANION_ID']);
            $companion->setFname($this->stringConverter->capitalize($dialogRow['COMPANION_FNAME']));
            $companion->setLname($this->stringConverter->capitalize($dialogRow['COMPANION_LNAME']));
            $companion->setPatronymic($this->stringConverter->capitalize($dialogRow['COMPANION_PTR']));

            $dialog = new Dialog();
            $dialog->setId($dialogRow['DIALOG']);
            $dialog->setCompanion($companion);
            $dialog->setHasUnread($dialogRow['UNREAD_COUNT'] != 0);
            $dialog->setUnreadCount($dialogRow['UNREAD_COUNT']);

            if($dialogRow['M_ID']) {
                $lastMessageSender = new Person();
                $lastMessageSender->setUoid($dialogRow['LMS_ID']);
                $lastMessageSender->setFname($this->stringConverter->capitalize($dialogRow['LMS_FNAME']));
                $lastMessageSender->setLname($this->stringConverter->capitalize($dialogRow['LMS_LNAME']));
                $lastMessageSender->setPatronymic($this->stringConverter->capitalize($dialogRow['LMS_PTR']));

                $lastMessage = new PrivateMessage();
                $lastMessage->setMessageText($dialogRow['LAST_MGS']);
                $lastMessage->setSendTime(new \DateTime($dialogRow['SEND_TIME']));
                $lastMessage->setId($dialogRow['M_ID']);
                $lastMessage->setChat($dialogRow['DIALOG']);

                if($lastMessageSender->getUoid() === $person) {
                    $lastMessage->setMeSender(true);
                } else {
                    $lastMessage->setSender($lastMessageSender);
                }

                $lastMessage->setIsRead($dialogRow['COMPANION_UNREAD'] == 0);

                if($dialogRow['ATT_SIZE'] && $dialogRow['DOCNAME']) {
                    $attachment = new Attachment();
                    $attachment->setAttachmentSize($dialogRow['ATT_SIZE']);
                    $attachment->setAttachmentName($dialogRow['DOCNAME']);
                    $lastMessage->setAttachments([$attachment]);
                }

                if($dialogRow['LINK'] && $dialogRow['LINK_TEXT']) {
                    $link = new ExternalLink();
                    $link->setLinkText($dialogRow['LINK_TEXT']);
                    $lastMessage->setLinks([$link]);
                }

                $dialog->setLastMessage($lastMessage);
            }

            $loadedDialogs[] = $dialog;
        }

        return $loadedDialogs;
    }

    /**
     * @param string $dialog
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getMessageCountInDialog(string $dialog): int
    {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('COUNT(EMCL.OID) AS CNT')
            ->from('ET_MSG_CHAT_LK', 'EMCL')
            ->where('EMCL.DIALOG = :DIALOG')
            ->setParameter('DIALOG', $dialog)
            ->execute()
            ->fetchAllAssociative();

        return $result[0]['CNT'];
    }

    /**
     * @param string $person
     * @param string $dialog
     * @param string|null $bound
     * @param int|null $count
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getMessageList(string $person, string $dialog, ?string $bound, ?int $count): array
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select("EMCL.OID AS M_ID, EMCL.AUTHOR AS SENDER,
               CASE WHEN EMCL.AUTHOR = DM.PERSON THEN 1 ELSE 0 END AS ME_SENDER,
               CASE WHEN EMCL.AUTHOR <> DM.PERSON THEN CASE WHEN DM.COMPANION_LAST_READ IS NOT NULL 
               AND DM.COMPANION_LAST_READ >= EMCL.NUM THEN 1 ELSE 0 END END AS IS_READ,
               NP.FNAME AS SENDER_FNAME, 
               NP.FAMILY AS SENDER_LNAME, 
               NP.MNAME AS SENDER_PTR,
               EMCL.CREATED AS SEND_TIME, 
               EMCL.NAME AS MSG, 
               EMCL.EXTLINK AS LINK, 
               EMCL.TEXTLINK AS LINKTEXT, 
               round(DBMS_LOB.getlength(EMCL.DOC) / 1024) AS DOCSIZE, 
               EMCL.FILE\$DOC AS DOCNAME"
            )
            ->from('ET_MSG_CHAT_LK', 'EMCL')
            ->innerJoin('EMCL', "(SELECT EDCL.OID AS DIALOG, EDCL.MEMBER1 AS PERSON, 
                TO_NUMBER(REGEXP_SUBSTR(EDCL.LAST_MSG_2, '[^\d:]\d+$')) AS COMPANION_LAST_READ FROM ET_DIALOG_CHAT_LK EDCL
                UNION ALL
                SELECT EDCL2.OID AS DIALOG, EDCL2.MEMBER2 AS PERSON, 
                TO_NUMBER(REGEXP_SUBSTR(EDCL2.LAST_MSG_1, '[^\d:]\d+$')) AS COMPANION_LAST_READ FROM ET_DIALOG_CHAT_LK EDCL2)", 'DM', 'EMCL.DIALOG = DM.DIALOG')
            ->innerJoin('EMCL', 'NPERSONS', 'NP', 'EMCL.AUTHOR = NP.OID')
            ->where('DM.DIALOG = :DIALOG_ID')
            ->andWhere('DM.PERSON = :PERSON_ID')
            ->orderBy('SEND_TIME', 'DESC')
            ->setMaxResults($count)
            ->setParameter('DIALOG_ID', $dialog)
            ->setParameter('PERSON_ID', $person);

        // with identifier paginator
        if($bound) {
            $edgeMessageNum = (int)substr($bound, strpos($bound, ':') + 1);
            $queryBuilder
                ->andWhere('EMCL.NUM > :EDGE_MESSAGE_NUM')
                ->setParameter('EDGE_MESSAGE_NUM', $edgeMessageNum);
            }

        $result = $queryBuilder->execute();

        $messageList = [];
        while($messageRow = $result->fetchAssociative()) {
            $sender = new Person();
            $sender->setUoid($messageRow['SENDER']);
            $sender->setFname($this->stringConverter->capitalize($messageRow['SENDER_FNAME']));
            $sender->setLname($this->stringConverter->capitalize($messageRow['SENDER_LNAME']));
            $sender->setPatronymic($this->stringConverter->capitalize($messageRow['SENDER_PTR']));

            $message = new PrivateMessage();
            $message->setId($messageRow['M_ID']);
            if($sender->getUoid() === $person) {
                $message->setMeSender(true);
            } else {
                $message->setSender($sender);
            }

            $message->setIsRead($messageRow['IS_READ']);
            $message->setSendTime(new \DateTime($messageRow['SEND_TIME']));
            $message->setMessageText($messageRow['MSG']);
            $message->setChat($dialog);

            if($messageRow['LINKTEXT'] && $messageRow['LINK']) {
                $link = new ExternalLink();
                $link->setLinkText($messageRow['LINKTEXT']);
                $link->setLinkContent($messageRow['LINK']);
                $message->setLinks([$link]);
            }

            if($messageRow['DOCSIZE'] && $messageRow['DOCNAME']) {
                $attachment = new Attachment();
                $attachment->setAttachmentName($messageRow['DOCNAME']);
                $attachment->setAttachmentSize($messageRow['DOCSIZE']);
                $message->setAttachments([$attachment]);
            }

            $messageList[] = $message;
        }

        return $messageList;
    }

    /**
     * @param string $senderPerson
     * @param string $dialog
     * @param string $message
     * @param BinaryFile[] $attachments
     * @param ExternalLink[] $links
     * @return String
     * @throws ConnectionException
     */
    public function addMessageToDialog(string $senderPerson,
                                       string $dialog, string $message, array $attachments, array $links): string
    {
        $conn = $this->getConnection();
        $queryBuilder = $conn->createQueryBuilder();
        $newOid = $this->getNewOid();

        $conn->beginTransaction();

        try {
            $queryBuilder
                ->insert('ET_MSG_CHAT_LK')
                ->setValue('OID', ':OID')
                ->setValue('AUTHOR', ':AUTHOR')
                ->setValue('CREATED', "TO_DATE(:CREATED_AT, 'yyyy-mm-dd hh24:mi:ss')")
                ->setValue('DIALOG', ':DIALOG')
                ->setValue('NUM', ':MESSAGE_NUM')
                ->setParameter('MESSAGE_NUM', substr($newOid, strpos($newOid, ':') + 1));

            if ($message) {
                $queryBuilder
                    ->setValue('NAME', ':MGS_TEXT')
                    ->setParameter('MGS_TEXT', $message);
            }

            if (count($attachments) > 0) {
                $queryBuilder
                    ->setValue('DOC', ':DOCUMENT')
                    ->setValue('FILE$DOC', 'DOC_NAME')
                    ->setParameter('DOCUMENT', $attachments[0]->getFileContent())
                    ->setParameter('DOC_NAME', $attachments[0]->getFilename());
            }

            if (count($links) > 0) {
                $queryBuilder
                    ->setValue('LINK', ':LINK')
                    ->setValue('TEXTLINK', ':LINK_TEXT')
                    ->setParameter('LINK', $links[0]->getLinkContent())
                    ->setParameter('LINK_TEXT', $links[0]->getLinkText());
            }

            $queryBuilder
                ->setParameter('OID', $newOid)
                ->setParameter('AUTHOR', $senderPerson)
                ->setParameter('CREATED_AT', (new \DateTime())->format('Y-m-d H:i:s'))
                ->setParameter('DIALOG', $dialog);

            $queryBuilder->execute();

            $this->updateLastViewedMessages($dialog, $senderPerson, $newOid);

            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollBack();
            throw new DataAccessException($e);
        }

        return $newOid;
    }

    /**
     * @param BinaryFile $binaryFile
     * @param string $messageId
     * @throws \Doctrine\DBAL\Exception
     */
    public function addPrivateMessageAttachment(BinaryFile $binaryFile, string $messageId)
    {
        $this->getConnection()->createQueryBuilder()
            ->update('ET_MSG_CHAT_LK')
            ->set('DOC', ':DOCUMENT')
            ->set('FILE$DOC', ':DOC_NAME')
            ->where('OID = :MESSAGE_ID')
            ->setParameter('DOCUMENT', $binaryFile->getFileContent(), 'blob')
            ->setParameter('DOC_NAME', $binaryFile->getFilename())
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute();
    }

    /**
     * @param string $messageId
     * @return BinaryFile
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPrivateMessageAttachment(string $messageId): BinaryFile {
        $result = $this->getConnection()->createQueryBuilder()
            ->select('EMSG.DOC, EMSG.FILE$DOC AS DOC_NAME')
            ->from('ET_MSG_CHAT_LK', 'EMSG')
            ->where('EMSG.OID = :MESSAGE_ID')
            ->setParameter('MESSAGE_ID', $messageId)
            ->execute()
            ->fetchAllAssociative();

        if(count($result) !== 1 || !($result[0]['DOC'] && $result[0]['DOC_NAME'])) {
            throw new NotFoundException('Message');
        }

        $file = new BinaryFile();
        $file->setFilename($result[0]['DOC_NAME']);
        $file->setFileContent($result[0]['DOC']);

        return $file;
    }

    /**
     * @param string $dialog
     * @param string $person
     * @param string $value
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateLastViewedMessages(string $dialog, string $person, string $value)
    {
        $queryBuilder = $this->getConnection()->createQueryBuilder();
        $queryBuilder
            ->update('ET_DIALOG_CHAT_LK', 'EDCL')
            ->set('LAST_MSG_1', 'CASE WHEN EDCL.MEMBER1 = :PERSON THEN :VALUE ELSE EDCL.LAST_MSG_1 END')
            ->set('LAST_MSG_2', 'CASE WHEN EDCL.MEMBER2 = :PERSON THEN :VALUE ELSE EDCL.LAST_MSG_2 END')
            ->where('EDCL.OID = :DIALOG')
            ->setParameter('DIALOG', $dialog)
            ->setParameter('PERSON', $person)
            ->setParameter('VALUE', $value)
            ->execute();
    }
}