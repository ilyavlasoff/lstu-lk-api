<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\QueryParam\Dialog;
use App\Model\QueryParam\Paginator;
use App\Model\QueryParam\Person;
use App\Model\DTO\ListedResponse;
use App\Model\QueryParam\PrivateMessage;
use App\Model\QueryParam\SendingPrivateMessage;
use App\Model\QueryParam\WithJsonFlag;
use App\Repository\PrivateMessageRepository;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PrivateMessagesController
 * @package App\Controller
 * @Route("/api/v1/messenger")
 */
class PrivateMessagesController extends AbstractRestController
{
    private $privateMessageRepository;

    public function __construct(SerializerInterface $serializer, PrivateMessageRepository $privateMessageRepository)
    {
        parent::__construct($serializer);
        $this->privateMessageRepository = $privateMessageRepository;
    }

    /**
     * @Route("/dialog/list", name="messenger_dialog_list", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function getDialogList(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $dialogs = $this->privateMessageRepository->getUserDialogs($user->getDbOid());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException();
        }

        $dialogList = new ListedResponse();
        $dialogList->setCount(count($dialogs));
        $dialogList->setPayload($dialogs);

        return $this->responseSuccessWithObject($dialogList);
    }

    /**
     * @Route("/dialog", name="messenger_dialog_add", methods={"POST"})
     *
     * @param Person $person
     * @return JsonResponse
     */
    public function startDialogWithUser(Person $person): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $createdDialogId = $this->privateMessageRepository->startDialog($user->getUsername(), $person->getPersonId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $createdDialog = new \App\Model\DTO\Dialog();
        $createdDialog->setId($createdDialogId);

        return $this->responseSuccessWithObject($createdDialog);
    }

    /**
     * @Route("/list", name="private_messages_list", methods={"GET"})
     *
     * @param Dialog $dialog
     * @param Paginator $paginator
     * @return JsonResponse
     */
    public function getPrivateMessageList(Dialog $dialog, Paginator $paginator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $offset = $paginator->getOffset();
        $count = $paginator->getCount();

        if($offset === null) {
            $offset = 0;
        }
        if($count === null) {
            $count = 100;
        }

        try {
            $totalMessagesCount = $this->privateMessageRepository->getMessageCountInDialog($dialog->getDialogId());

            $messages = $this->privateMessageRepository->getMessageList(
                $user->getDbOid(), $dialog->getDialogId(), $offset, $count);

            //TODO: Add increasing read message
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $messageList = new ListedResponse();
        $messageList->setPayload($messages);
        $messageList->setCount(count($messages));
        $messageList->setOffset($offset);

        $remains = $totalMessagesCount - $offset - count($messages);
        $messageList->setRemains($remains);

        if($remains > 0) {
            $messageList->setNextOffset($offset + count($messages));
        }

        return $this->responseSuccessWithObject($messageList);
    }

    /**
     * @Route("", name="private_messages_add", methods={"POST"})
     * @param SendingPrivateMessage $privateMessage
     * @param Dialog $dialog
     * @param WithJsonFlag $jsonFlag
     * @return JsonResponse
     */
    public function addNewPrivateMessage(
        SendingPrivateMessage $privateMessage,
        Dialog $dialog,
        WithJsonFlag $jsonFlag
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $dialogParticipants = $this->privateMessageRepository->getDialogParticipants($dialog->getDialogId());

            if(!in_array($user->getDbOid(), $dialogParticipants)) {
                throw new AccessDeniedException('Dialog');
            }

        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $attachments = [];
        if($jsonFlag->getWithJsonData()) {
            $attachments = array_map(function (Attachment $attachment) {
                return $attachment->toBinary();
            }, $privateMessage->getAttachments());
        }

        try {
            $createdMessageId = $this->privateMessageRepository->addMessageToDialog(
                $user->getDbOid(),
                $dialog->getDialogId(),
                $privateMessage->getMessage(),
                $attachments,
                $privateMessage->getExtLinks()
            );
        } catch (ConnectionException $e) {
            throw new DataAccessException($e);
        }

        $createdMessage = new \App\Model\DTO\PrivateMessage();
        $createdMessage->setId($createdMessageId);

        return $this->responseCreated($createdMessage);
    }

    /**
     * @Route("/doc", name="private_message_attachment_add", methods={"POST"})
     *
     * @param BinaryFile $binaryFile
     * @param PrivateMessage $privateMessage
     * @return JsonResponse
     */
    public function addPrivateMessageAttachment(BinaryFile $binaryFile, PrivateMessage $privateMessage): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $sender =  $this->privateMessageRepository->getMessageSender($privateMessage->getMsg());

            if($user->getDbOid() !== $sender)
            {
                throw new AccessDeniedException('Message');
            }

            $this->privateMessageRepository->addPrivateMessageAttachment($binaryFile, $privateMessage->getMsg());

        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccess();
    }
}