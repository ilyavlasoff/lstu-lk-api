<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\Request\Dialog;
use App\Model\Request\Paginator;
use App\Model\Request\Person;
use App\Model\Response\ListedResponse;
use App\Repository\PrivateMessageRepository;
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
     * @Route("/dialog/list", name="get_dialog_list", methods={"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getDialogList(): JsonResponse
    {
        /** @var \App\Document\User $user */
        $user = $this->getUser();

        try {
            $dialogs = $this->privateMessageRepository->getUserDialogs($user->getDbOid());
        } catch (Exception $e) {
            throw new DataAccessException();
        }

        $dialogList = new ListedResponse();
        $dialogList->setCount(count($dialogs));
        $dialogList->setPayload($dialogs);

        return $this->responseSuccessWithObject($dialogList);
    }

    /**
     * @Route("/dialog", name="start_dialog", methods={"POST"})
     *
     * @param \App\Model\Request\Person $person
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function startDialogWithUser(Person $person): JsonResponse
    {
        /** @var \App\Document\User $user */
        $user = $this->getUser();

        try {
            $createdDialog = $this->privateMessageRepository->startDialog($user->getUsername(), $person->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($createdDialog);
    }

    /**
     * @Route("/history", name="private_messages_list", methods={"GET"})
     *
     * @param \App\Model\Request\Dialog $dialog
     * @param \App\Model\Request\Paginator $paginator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getPrivateMessageList(Dialog $dialog, Paginator $paginator): JsonResponse
    {
        /** @var \App\Document\User $user */
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
        } catch (Exception $e) {
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
     * @Route("/add", name="add_new_message", methods={"GET"})
     */
    public function addNewPrivateMessage()
    {

    }
}