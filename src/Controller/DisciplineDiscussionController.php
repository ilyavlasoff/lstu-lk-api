<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\DiscussionMessage;
use App\Model\DTO\Group;
use App\Model\DTO\IdentifierBoundedListedResponse;
use App\Model\DTO\ListedResponse;
use App\Model\QueryParam\Discipline;
use App\Model\QueryParam\Education;
use App\Model\QueryParam\DisciplineDiscussionMessage;
use App\Model\QueryParam\IdentifierPaginator;
use App\Model\QueryParam\Paginator;
use App\Model\QueryParam\Semester;
use App\Model\QueryParam\SendingDiscussionMessage;
use App\Repository\DisciplineDiscussionRepository;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DisciplineDiscussionController
 * @package App\Controller
 * @Route("/api/v1/discussion")
 */
class DisciplineDiscussionController extends AbstractRestController
{
    private $disciplineDiscussionRepository;

    public function __construct(
        SerializerInterface $serializer,
        DisciplineDiscussionRepository $disciplineDiscussionRepository
    ){
        parent::__construct($serializer);
        $this->disciplineDiscussionRepository = $disciplineDiscussionRepository;
    }

    /**
     * @Route("/list", name="discipline_discussion_list", methods={"GET"})
     *
     * @param Discipline $discipline
     * @param Education $education
     * @param Semester $semester
     * @param IdentifierPaginator $paginator
     * @param EducationRepository $educationRepository
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function getDisciplineChatMessages(
        Discipline $discipline,
        Education $education,
        Semester $semester,
        IdentifierPaginator $paginator,
        EducationRepository $educationRepository,
        PersonalRepository $personalRepository
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $currentUserEduList = $educationRepository->getUserEducationsIdList($user->getDbOid());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException();
        }

        if (!in_array($education->getEducationId(), array_values($currentUserEduList))) {
            throw new AccessDeniedException('Chat');
        }

        try {
            $group = $personalRepository->getGroupByContingent($education->getEducationId());

            $disciplineChatMessages = $this->disciplineDiscussionRepository
                ->getDisciplineChatMessages($semester->getSemesterId(), $discipline->getDisciplineId(),
                    $group, $paginator->getEdge(), $paginator->getCount());

            $remains = 0;
            $lastMessage = null;
            if(count($disciplineChatMessages) > 0) {
                /** @var DiscussionMessage $lastMessage */
                $lastMessage = $disciplineChatMessages[count($disciplineChatMessages) - 1];
                $remains = $this->disciplineDiscussionRepository->getOlderDiscussionListCountThanSpecified(
                    $semester->getSemesterId(), $discipline->getDisciplineId(), $group, $lastMessage->getId());
            }
        } catch (Exception | \Doctrine\DBAL\Driver\Exception | \Exception $e) {
            throw new DataAccessException($e);
        }

        $discussionChatList = new ListedResponse();
        $discussionChatList->setPayload($disciplineChatMessages);
        $discussionChatList->setOffset($paginator->getEdge());
        $discussionChatList->setCount(count($disciplineChatMessages));
        $discussionChatList->setRemains($remains);
        if($lastMessage) {
            $discussionChatList->setNextOffset($lastMessage->getId());
        }


        return $this->responseSuccessWithObject($discussionChatList);
    }

    /**
     * @Route("", name="discipline_discussion_message_add", methods={"POST"})
     *
     * @param SendingDiscussionMessage $message
     * @param Education $education
     * @param Discipline $discipline
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function addDisciplineChatMessage(
        SendingDiscussionMessage $message,
        Education $education,
        Discipline $discipline,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $group = $personalRepository->getGroupByContingent($education->getEducationId());
            $isAllowed = $this->disciplineDiscussionRepository->getUserHasPermissionsToChat(
                $user->getDbOid(), $semester->getSemesterId(), $group, $discipline->getDisciplineId());

            if(!$isAllowed) {
                throw new AccessDeniedException('Discipline chat');
            }
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $messageText = $message->getMsg();
        $attachments = array_map(function (Attachment $att) {
            return $att->toBinary();
        }, $message->getAttachments());
        $links = $message->getExternalLinks();

        try {
            $msgId = $this->disciplineDiscussionRepository->addNewDisciplineDiscussionMessage(
                $messageText,
                $attachments,
                $links,
                $user->getDbOid(),
                $semester->getSemesterId(),
                $discipline->getDisciplineId(),
                $group
            );
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $addedMessage = new DiscussionMessage();
        $addedMessage->setId($msgId);

        return $this->responseCreated($addedMessage);
    }

    /**
     * @Route("/doc", name="discipline_discussion_message_attachment_add", methods={"POST"})
     *
     * @param BinaryFile $file
     * @param DisciplineDiscussionMessage $message
     * @return JsonResponse
     */
    public function addDisciplineChatAttachment(BinaryFile $file, DisciplineDiscussionMessage $message): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $isBelongs = $this->disciplineDiscussionRepository->isMessageBelongsToUser($message->getMsg(), $user->getDbOid());

            if(!$isBelongs) {
                throw new AccessDeniedException('DisciplineDiscussionMessage');
            }

            $this->disciplineDiscussionRepository->addAttachmentToMessage($message->getMsg(), $file);
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccess();
    }
}