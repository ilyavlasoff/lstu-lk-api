<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\DiscussionMessage;
use App\Model\DTO\Group;
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
use App\Service\RabbitmqTest;
use App\Service\StringConverter;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

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
     * @OA\Get(
     *     tags={"Обсуждения дисциплин"},
     *     summary="Получение списка сообщений в обсуждении дисциплины с данными о пагинации",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="dis",
     *          description="Идентификатор дисциплины"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="sem",
     *          description="Идентификатор учебного семестра"
     *     ),
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
     *          description="Ммаксимальное количество отдаваемых элементов на одной странице ответа"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Cтраницa списка сообщений обсуждения дисциплины",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="offset", type="integer"),
     *              @OA\Property(property="next_offset", type="integer"),
     *              @OA\Property(property="remains", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\DiscussionMessage::class, groups={"Default"})))
     *          ))
     *     )
     * )
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
     * @OA\Post(
     *     tags={"Обсуждения дисциплин"},
     *     summary="Добавление нового сообщения в обсуждение дисциплины",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="dis",
     *          description="Идентификатор дисциплины"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="sem",
     *          description="Идентификатор учебного семестра"
     *     ),
     *     @OA\RequestBody(
     *          description="Объект нового сообщения в дисциплине",
     *          @OA\JsonContent(
     *              ref=@Model(type=SendingDiscussionMessage::class, groups={"Default"})
     *          )
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Успешно добавлено",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="id",
     *                  nullable=false,
     *                  type="string",
     *                  description="Идентификатор добавленного сообщения"
     *              )
     *          )
     *     )
     * )
     *
     * @param SendingDiscussionMessage $message
     * @param Education $education
     * @param Discipline $discipline
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @param RabbitmqTest $rabbitmqTest
     * @param StringConverter $stringConverter
     * @return JsonResponse
     */
    public function addDisciplineChatMessage(
        SendingDiscussionMessage $message,
        Education $education,
        Discipline $discipline,
        Semester $semester,
        PersonalRepository $personalRepository,
        RabbitmqTest $rabbitmqTest,
        StringConverter $stringConverter
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

            // TEST RABBIT MQ

            $data = $this->disciplineDiscussionRepository->getNewCreatedDiscussionMessageData($msgId);

            try {
                $created = $data['CREATED'] ? (new \DateTime($data['CREATED']))->format('y-m-d H:i:s') : null;
            } catch (\Exception $e) {
                $created = null;
            }

            if($data) {
                $rabbitmqTest->notifyDiscussionMessageCreated($data['OID'], $data['G'], $data['DISCIPLINE'], $data['CSEMESTER'],
                    $data['AUTHOR'], $stringConverter->capitalize($data['FNAME']), $stringConverter->capitalize($data['FAMILY']),
                    $stringConverter->capitalize($data['MNAME']), $data['MSG'], $created,
                    $data['DOCNAME'], $data['DOCSIZE'], $data['TEXTLINK'], $data['EXTLINK']);

            }

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
     * @OA\Post(
     *     tags={"Обсуждения дисциплин"},
     *     summary="Добавление документа к сообщению в обсуждении",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="msg",
     *          description="Идентификатор сообщения в обсуждении"
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
     * @param BinaryFile $file
     * @param DisciplineDiscussionMessage $message
     * @return JsonResponse
     */
    public function addDisciplineChatAttachment(BinaryFile $file, DisciplineDiscussionMessage $message, StringConverter $stringConverter, RabbitmqTest $rabbitmqTest): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $isBelongs = $this->disciplineDiscussionRepository->isMessageBelongsToUser($message->getMsg(), $user->getDbOid());

            if(!$isBelongs) {
                throw new AccessDeniedException('DisciplineDiscussionMessage');
            }

            $this->disciplineDiscussionRepository->addAttachmentToMessage($message->getMsg(), $file);

            $data = $this->disciplineDiscussionRepository->getNewCreatedDiscussionMessageData($message->getMsg());

            try {
                $created = $data['CREATED'] ? (new \DateTime($data['CREATED']))->format('y-m-d H:i:s') : null;
            } catch (\Exception $e) {
                $created = null;
            }

            if($data) {
                $rabbitmqTest->notifyDiscussionMessageChanged($data['OID'], $data['G'], $data['DISCIPLINE'], $data['CSEMESTER'],
                    $data['AUTHOR'], $stringConverter->capitalize($data['FNAME']), $stringConverter->capitalize($data['FAMILY']),
                    $stringConverter->capitalize($data['MNAME']), $data['MSG'], $created,
                    $data['DOCNAME'], $data['DOCSIZE'], $data['TEXTLINK'], $data['EXTLINK']);

            }
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccess();
    }
}