<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Exception\DuplicateValueException;
use App\Exception\NotFoundException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\IdentifierBoundedListedResponse;
use App\Model\QueryParam\Dialog;
use App\Model\QueryParam\IdentifierPaginator;
use App\Model\QueryParam\Paginator;
use App\Model\QueryParam\Person;
use App\Model\DTO\ListedResponse;
use App\Model\QueryParam\PrivateMessage;
use App\Model\QueryParam\SendingPrivateMessage;
use App\Model\QueryParam\WithJsonFlag;
use App\Repository\PrivateMessageRepository;
use App\Service\RabbitmqTest;
use App\Service\StringConverter;
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
     * @param IdentifierPaginator $paginator
     * @return JsonResponse
     */
    public function getDialogList(IdentifierPaginator $paginator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $bound = $paginator->getEdge();
        $count = $paginator->getCount();

        try {
            /** @var \App\Model\DTO\Dialog[] $dialogs */
            $dialogs = $this->privateMessageRepository->getUserDialogs($user->getDbOid(), $bound, $count);

            $remains = 0;
            $lastDialog = null;
            if($cnt = count($dialogs)) {
                $lastDialog = $dialogs[$cnt - 1];
                $remains = $this->privateMessageRepository->getOlderDialogsThanSpecified($user->getDbOid(), $lastDialog->getId());
            }
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException();
        }

        $dialogList = new ListedResponse();
        $dialogList->setCount(count($dialogs));
        $dialogList->setPayload($dialogs);
        $dialogList->setOffset($bound);
        $dialogList->setRemains($remains);
        if($remains) {
            $dialogList->setNextOffset($lastDialog->getId());
        }

        return $this->responseSuccessWithObject($dialogList);
    }

    /**
     * @Route("/dialog/ids/list", name="messenger_dialog_ids_full_list", methods={"GET"})
     * @return JsonResponse
     */
    public function getDialogIdentifiersFullList() {
        /** @var User $user */
        $user = $this->getUser();

        try {
            /** @var \App\Model\DTO\Dialog[] $dialogsIdsList */
            $dialogsIdsList = $this->privateMessageRepository->getDialogIdentifiersList($user->getDbOid());
        } catch (\Doctrine\DBAL\Driver\Exception | Exception $e) {
            throw new DataAccessException($e);
        }

        $response = new ListedResponse();
        $response->setPayload($dialogsIdsList);
        $response->setCount(count($dialogsIdsList));
        $response->setOffset(0);
        $response->setRemains(0);

        return $this->responseSuccessWithObject($response);
    }

    /**
     * @Route("/dialog", name="messenger_dialog_add", methods={"POST"})
     *
     * @param Person $companion
     * @param RabbitmqTest $rabbitmqTest
     * @param StringConverter $stringConverter
     * @return JsonResponse
     */
    public function startDialogWithUser(Person $companion, RabbitmqTest $rabbitmqTest, StringConverter $stringConverter): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            if(count($existingDialogs = $this->privateMessageRepository
                    ->getExistingDialogId($user->getDbOid(), $companion->getPersonId())) > 0) {
                $requestedDialogId = $existingDialogs[0];
            } else {

                $requestedDialogId = $this->privateMessageRepository->startDialog($user->getDbOid(), $companion->getPersonId());

                // TEST RABBIT MQ
                $data = $this->privateMessageRepository->getNewCreatedDialogInfo($requestedDialogId);

                try {
                    $created = $data['CREATED'] ? (new \DateTime($data['CREATED']))->format('y-m-d H:i:s') : null;
                } catch (\Exception $e) {
                    $created = null;
                }

                if ($data) {
                    $rabbitmqTest->notifyDialogCreated($data['MEMBER1'], $data['MEMBER2'],
                        $stringConverter->capitalize($data['FN1']), $stringConverter->capitalize($data['LN1']),
                        $stringConverter->capitalize($data['P1']), $stringConverter->capitalize($data['FN2']),
                        $stringConverter->capitalize($data['LN2']), $stringConverter->capitalize($data['P2']),
                        $data['DIAL_ID'], $data['UNREAD1'], $data['UNREAD2'], $data['MAX_MSG'], $data['TXT'], $data['AUTHOR'], $created);
                }

            }
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $createdDialog = new \App\Model\DTO\Dialog();
        $createdDialog->setId($requestedDialogId);

        return $this->responseCreated($createdDialog);
    }

    /**
     * @Route("/list", name="private_messages_list", methods={"GET"})
     *
     * @param Dialog $dialog
     * @param IdentifierPaginator $paginator
     * @return JsonResponse
     */
    public function getPrivateMessageList(Dialog $dialog, IdentifierPaginator $paginator): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $bound = $paginator->getEdge();
        $count = $paginator->getCount();

        if($bound) {
            try {
                $isExists = $this->privateMessageRepository->getMessageExists($bound);
                $belongs = $this->privateMessageRepository->getDialogByMessage($bound);
                if(!$isExists || $dialog->getDialogId() !== $belongs) {
                    throw new NotFoundException('Private message');
                }
            } catch (\Doctrine\DBAL\Driver\Exception | Exception $e) {
                throw new DataAccessException($e);
            }
        }

        if(!$count) {
            $count = 100;
        }

        try {
            $messages = $this->privateMessageRepository->getMessageList(
                $user->getDbOid(), $dialog->getDialogId(), $bound, $count);


            $lastMessage = null;
            $remains = 0;
            if(count($messages)) {
                /** @var \App\Model\DTO\PrivateMessage $lastMessage */
                $lastMessage = $messages[count($messages) - 1];
                $this->privateMessageRepository->updateLastViewedMessages(
                    $dialog->getDialogId(), $user->getDbOid(), $lastMessage->getId());
                $remains = $this->privateMessageRepository->getOlderMessagesThanSpecified($dialog->getDialogId(), $lastMessage->getId());
            }
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $messageList = new ListedResponse();
        $messageList->setPayload($messages);
        $messageList->setCount(count($messages));
        $messageList->setOffset($bound);
        $messageList->setRemains($remains);
        if($lastMessage) {
            $messageList->setNextOffset($lastMessage->getId());
        }

        return $this->responseSuccessWithObject($messageList);
    }

    /**
     * @Route("", name="private_messages_add", methods={"POST"})
     * @param SendingPrivateMessage $privateMessage
     * @param Dialog $dialog
     * @param WithJsonFlag $jsonFlag
     * @param RabbitmqTest $rabbitmqTest
     * @param StringConverter $stringConverter
     * @return JsonResponse
     */
    public function addNewPrivateMessage(
        SendingPrivateMessage $privateMessage,
        Dialog $dialog,
        WithJsonFlag $jsonFlag,
        RabbitmqTest $rabbitmqTest,
        StringConverter $stringConverter
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

            // TEST RABBIT MQ

            $data = $this->privateMessageRepository->getNewCreatedMessageInfo($createdMessageId);

            try {
                $created = $data['CREATED'] ? (new \DateTime($data['CREATED']))->format('y-m-d H:i:s') : null;
            } catch (\Exception $e) {
                $created = null;
            }

            if($data) {
                $rabbitmqTest->notifyPrivateMessageCreated($data['DIALOG'], $data['MEMBER1'], $data['MEMBER2'], $data['MEMBER1READ'],
                    $data['MEMBER2READ'], $data['OID'], $data['AUTHOR'], $stringConverter->capitalize($data['FNAME']),
                    $stringConverter->capitalize($data['FAMILY']), $stringConverter->capitalize($data['MNAME']),
                    $data['NAME'], $created , $data['DOCNAME'], $data['DOCSIZE'], $data['TEXTLINK'], $data['EXTLINK'], $data['NUM']);
            }

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
     * @param RabbitmqTest $rabbitmqTest
     * @param StringConverter $stringConverter
     * @return JsonResponse
     */
    public function addPrivateMessageAttachment(
        BinaryFile $binaryFile,
        PrivateMessage $privateMessage,
        RabbitmqTest $rabbitmqTest,
        StringConverter $stringConverter
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $sender =  $this->privateMessageRepository->getMessageSender($privateMessage->getMsg());

            if($user->getDbOid() !== $sender)
            {
                throw new AccessDeniedException('Message');
            }

            $this->privateMessageRepository->addPrivateMessageAttachment($binaryFile, $privateMessage->getMsg());

            // TEST RABBIT MQ

            $data = $this->privateMessageRepository->getNewCreatedMessageInfo($privateMessage->getMsg());

            try {
                $created = $data['CREATED'] ? (new \DateTime($data['CREATED']))->format('y-m-d H:i:s') : null;
            } catch (\Exception $e) {
                $created = null;
            }

            if($data) {
                $rabbitmqTest->notifyPrivateMessageChanged($data['DIALOG'], $data['MEMBER1'], $data['MEMBER2'], $data['MEMBER1READ'],
                    $data['MEMBER2READ'], $data['OID'], $data['AUTHOR'], $stringConverter->capitalize($data['FNAME']),
                    $stringConverter->capitalize($data['FAMILY']), $stringConverter->capitalize($data['MNAME']),
                    $data['NAME'], $created , $data['DOCNAME'], $data['DOCSIZE'], $data['TEXTLINK'], $data['EXTLINK'], $data['NUM']);
            }

        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccess();
    }

}