<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Exception\DuplicateValueException;
use App\Exception\NotFoundException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
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
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

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
     * @OA\Get(
     *     tags={"Личные сообщения"},
     *     summary="Страница списка диалогов с данными о пагинации",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="iof",
     *          description="Последний ранее загруженный элемент"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="ic",
     *          description="Максимальное количество отдаваемых элементов на одной странице ответа"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Страница списка диалогов пользователя",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="offset", type="integer"),
     *              @OA\Property(property="next_offset", type="integer"),
     *              @OA\Property(property="remains", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\Dialog::class, groups={"Default"})))
     *          ))
     *     )
     * )
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
     *
     * @OA\Get(
     *     tags={"Личные сообщения"},
     *     summary="Список идентфикаторов диалогов пользователей",
     *     @Security(name="Bearer"),
     *     @OA\Response(
     *          response="200",
     *          description="Список идентификаторов диалогов пользователя",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(@OA\Property(property="id", description="Идентификатор диалога", nullable=false, example="5:956985465")))
     *          ))
     *     )
     * )
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
     * @OA\Post(
     *     tags={"Личные сообщения"},
     *     summary="Создание нового диалога",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="p",
     *          description="Идентификатор пользователя, с которым создается диалог"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешное создание диалога",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="Идентификатор диалога",
     *                  example="5:43565455"
     *              )
     *          )
     *     )
     * )
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
     * @OA\Get(
     *     tags={"Личные сообщения"},
     *     summary="Страница списка личных сообщений с данными о пагинации",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="iof",
     *          description="Последний ранее загруженный элемент"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="ic",
     *          description="Максимальное количество отдаваемых элементов на одной странице ответа"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Страница списка личных сообщений пользователя",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="offset", type="integer"),
     *              @OA\Property(property="next_offset", type="integer"),
     *              @OA\Property(property="remains", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\PrivateMessage::class, groups={"Default"})))
     *          ))
     *     )
     * )
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
                /** @var PrivateMessage $firstMessage */
                $firstMessage = $messages[0];
                $this->privateMessageRepository->updateLastViewedMessages(
                    $dialog->getDialogId(), $user->getDbOid(), $firstMessage->getId());

                /** @var \App\Model\DTO\PrivateMessage $lastMessage */
                $lastMessage = $messages[count($messages) - 1];
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
     *
     * @OA\Post(
     *     tags={"Личные сообщения"},
     *     summary="Добавление нового сообщения",
     *     @Security(name="Bearer"),
     *     @OA\RequestBody(
     *          required=true,
     *          description="Объект нового сообщения",
     *          @OA\JsonContent(ref=@Model(type=SendingPrivateMessage::class), description="Объект добавляемого сообщения", nullable=false)
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="dialog",
     *          description="Диалог, в который добавляется сообщение"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Успешное добавление сообщения в диалог",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  type="string",
     *                  description="Идентификатор добавленного сообщения",
     *                  example="5:43565455"
     *              )
     *          )
     *     )
     * )
     *
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

            $this->privateMessageRepository->updateLastViewedMessages(
                $dialog->getDialogId(), $user->getDbOid(), $createdMessageId);

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

        } catch (ConnectionException | Exception $e) {
            throw new DataAccessException($e);
        }

        $createdMessage = new \App\Model\DTO\PrivateMessage();
        $createdMessage->setId($createdMessageId);

        return $this->responseCreated($createdMessage);
    }

    /**
     * @Route("/doc", name="private_message_attachment_add", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Личные сообщения"},
     *     summary="Добавление документа к личному сообщению",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="pmsg",
     *          description="Идентификатор личного сообщения"
     *     ),
     *     @OA\RequestBody(
     *          description="Медиа-файл, добавляемый к сообщению",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Property(
     *                  property="attachment",
     *                  type="file",
     *                  description="Файл, добавляемый к сообщению"
     *              )
     *          )
     *      ),
     *     @OA\Response(
     *          response="200",
     *          description="Медиа-файл успешно добавлен"
     *      )
     * )
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