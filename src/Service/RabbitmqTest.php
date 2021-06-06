<?php

namespace App\Service;

use App\Document\User;
use App\Model\DTO\Dialog;
use App\Model\DTO\Discipline;
use App\Model\DTO\DiscussionMessage;
use App\Model\DTO\Group;
use App\Model\DTO\Semester;
use App\Model\QueryParam\PrivateMessage;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RabbitmqTest extends AbstractQueryService
{
    private $urlBase;

    public function __construct(HttpClientInterface $httpClient, SerializerInterface $serializer, ParameterBagInterface $parameterBag)
    {
        parent::__construct($httpClient, $serializer);
        $this->urlBase = $parameterBag->get('notifier_base_url');
    }

    public function notifyAboutCreatingDialog(
        string $member1Id,
        string $member2Id,
        string $member1Name,
        string $member1Surname,
        string $member1Patronymic,
        string $member2Name,
        string $member2Surname,
        string $member2Patronymic,
        string $dialogId,
        string $unread1Count,
        string $unread2Count,
        string $lastMessageId,
        string $lastMessageText,
        string $lastAuthor,
        \DateTime $lastSendTime
    ) {
        $this->makeQuery($this->urlBase, 'data/dialog-created', 'POST', 'http', 200, [], [], [
            'member1_id' => $member1Id,
            'member2_id' => $member2Id,
            'member1_name' => $member1Name,
            'member1_surname' => $member1Surname,
            'member1_patronymic' => $member1Patronymic,
            'member2_name' => $member2Name,
            'member2_surname' => $member2Surname,
            'member2_patronymic' => $member2Patronymic,
            'dialog_id' => $dialogId,
            'unread1_count' => $unread1Count,
            'unread2_count' => $unread2Count,
            'last_message_id' => $lastMessageId,
            'last_message_text' => $lastMessageText,
            'last_author' => $lastAuthor,
            'last_send_time' => $lastSendTime
        ], null, '', false, '', false);
    }

    public function notifyAboutMessageReading(
        string $dialog,
        string $member1,
        string $member2,
        bool $member1Read,
        bool $member2Read,
        string $messageId,
        string $senderId,
        string $senderName,
        string $senderSurname,
        string $senderPatronymic,
        string $textContent,
        \DateTime $createdAt,
        string $docName,
        string $docSize,
        string $linkText,
        string $linkContent,
        int $messageNumber
    ) {
        $this->makeQuery($this->urlBase, 'data/msg-read', 'POST', 'http', 200, [], [], [
            'dialog' => $dialog,
            'member1' => $member1,
            'member2' => $member2,
            'member1_read' => $member1Read,
            'member2_read' => $member2Read,
            'message_id' => $messageId,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'sender_surname' => $senderSurname,
            'sender_patronymic' => $senderPatronymic,
            'text_content' => $textContent,
            'created_at' => $createdAt,
            'doc_name' => $docName,
            'doc_size' => $docSize,
            'link_text' => $linkText,
            'link_content' => $linkContent,
            'message_number' => $messageNumber
        ], null, '', false, '', false);
    }

    public function notifyAboutDiscussionMessage(
        string $id,
        string $group,
        string $discipline,
        string $semester,
        string $senderId,
        string $senderName,
        string $senderSurname,
        string $senderPatronymic,
        string $textContent,
        \DateTime $createdAt,
        string $docName,
        string $docSize,
        string $linkText,
        string $linkContent
    ) {
        $this->makeQuery($this->urlBase, 'data/discussion-msg-created', 'POST', 'http', 200, [], [], [
            'id' => $id,
            'group' => $group,
            'discipline' => $discipline,
            'semester' => $semester,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'sender_surname' => $senderSurname,
            'sender_patronymic' => $senderPatronymic,
            'text_content' => $textContent,
            'created_at' => $createdAt,
            'doc_name' => $docName,
            'doc_size' => $docSize,
            'link_text' => $linkText,
            'link_content' => $linkContent,
        ], null, '', false, '', false);
    }

    public function notifyAboutPrivateMessage(
        string $dialog,
        string $member1,
        string $member2,
        bool $member1Read,
        bool $member2Read,
        string $messageId,
        string $senderId,
        string $senderName,
        string $senderSurname,
        string $senderPatronymic,
        string $textContent,
        \DateTime $createdAt,
        string $docName,
        string $docSize,
        string $linkText,
        string $linkContent,
        int $messageNumber
    ) {
        $this->makeQuery($this->urlBase, 'data/message-created', 'POST', 'http', 200, [], [], [
            'dialog' => $dialog,
            'member1' => $member1,
            'member2' => $member2,
            'member1_read' => $member1Read,
            'member2_read' => $member2Read,
            'message_id' => $messageId,
            'sender_id' => $senderId,
            'sender_name' => $senderName,
            'sender_surname' => $senderSurname,
            'sender_patronymic' => $senderPatronymic,
            'text_content' => $textContent,
            'created_at' => $createdAt,
            'doc_name' => $docName,
            'doc_size' => $docSize,
            'link_text' => $linkText,
            'link_content' => $linkContent,
            'message_number' => $messageNumber
        ], null, '', false, '', false);
    }
}