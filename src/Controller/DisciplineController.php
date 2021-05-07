<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Mapping\Attachment;
use App\Model\Mapping\BinaryFile;
use App\Model\Mapping\DiscussionMessage;
use App\Model\Mapping\TimetableItem;
use App\Model\Request\Discipline;
use App\Model\Request\Education;
use App\Model\Request\Message;
use App\Model\Request\Paginator;
use App\Model\Request\Semester;
use App\Model\Request\SendingDiscussionMessage;
use App\Model\Request\WithJsonFlag;
use App\Model\Response\ListedResponse;
use App\Model\Response\Timetable;
use App\Repository\DisciplineRepository;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\BlobType;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

/**
 * Class DisciplineController
 * @package App\Controller
 * @Route("/api/v1/student/discipline")
 */
class DisciplineController extends AbstractRestController
{
    private $disciplineRepository;

    public function __construct(SerializerInterface $serializer, DisciplineRepository $disciplineRepository)
    {
        parent::__construct($serializer);
        $this->disciplineRepository = $disciplineRepository;
    }

    /**
     * @Route("/", name="discipline", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Дисциплины"},
     *     summary="Информация об учебной дисциплине",
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
     *          description="Идентификатор семестра"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Список объектов публикаций пользователя",
     *          @OA\JsonContent(ref=@Model(type="PublicationList::class", groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Некорректные параметры вызова"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Запрошенное значение не найдено"
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Внутренняя ошибка"
     *     )
     * )
     * @param Discipline $discipline
     * @return JsonResponse
     */
    public function discipline(Discipline $discipline): JsonResponse
    {
        try {
            $discipline = $this->disciplineRepository->getDiscipline($discipline->getDisciplineId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($discipline);
    }

    /**
     * @Route("/teachers", name="discipline-teachers", methods={"GET"})
     */
    public function disciplineTeachers(
        Discipline $discipline,
        Education $education,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse {
        try {
            $group = $personalRepository->getGroupByContingent($education->getEducationId());
            $teachersDiscipline = $this->disciplineRepository
                ->getTeachersByDiscipline($discipline->getDisciplineId(), $group, $semester->getSemesterId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $disciplineTeachersList = new ListedResponse();
        $disciplineTeachersList->setCount(count($teachersDiscipline));
        $disciplineTeachersList->setPayload($teachersDiscipline);

        return $this->responseSuccessWithObject($disciplineTeachersList);
    }

    /**
     * @Route("/timetable", name="discipline-timetable", methods={"GET"})
     */
    public function disciplineTimetable(
        Discipline $discipline,
        Education $education,
        Semester $semester,
        TimetableRepository $timetableRepository,
        PersonalRepository $personalRepository
    ): JsonResponse {
        try {
            $group = $personalRepository->getGroupByContingent($education->getEducationId());
            $timetableForDiscipline = $timetableRepository
                ->getTimetable($group, $semester->getSemesterId(), null, $discipline->getDisciplineId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($timetableForDiscipline);
    }

    /**
     * @Route("/chat", name="discipline-chat", methods={"GET"})
     */
    public function disciplineChatMessages(
        Discipline $discipline,
        Education $education,
        Semester $semester,
        Paginator $paginator,
        EducationRepository $educationRepository,
        PersonalRepository $personalRepository
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $currentUserEduList = $educationRepository->getUserEducationsIdList($user->getDbOid());
        } catch (Exception $e) {
            throw new DataAccessException();
        }

        if (!in_array($education->getEducationId(), array_values($currentUserEduList))) {
            throw new AccessDeniedException('Chat');
        }

        try {
            $group = $personalRepository->getGroupByContingent($education->getEducationId());

            $disciplineChatMessages = $this->disciplineRepository
                ->getDisciplineChatMessages($semester->getSemesterId(), $discipline->getDisciplineId(),
                    $group, $paginator->getOffset(), $paginator->getCount());

            $totalMessageCount = $this->disciplineRepository
                ->getDisciplineChatMessagesCount($group, $semester->getSemesterId(), $discipline->getDisciplineId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $remainsCount = $totalMessageCount - $paginator->getOffset() - count($disciplineChatMessages);

        $discussionChatList = new ListedResponse();
        $discussionChatList->setPayload($disciplineChatMessages);
        $discussionChatList->setOffset($paginator->getOffset());
        $discussionChatList->setCount(count($disciplineChatMessages));
        $discussionChatList->setRemains($remainsCount);

        if($remainsCount > 0) {
            $discussionChatList->setNextOffset($paginator->getOffset() + count($disciplineChatMessages));
        }

        return $this->responseSuccessWithObject($discussionChatList);
    }

    /**
     * @Route("/chat/add", name="discipline-chat-add", methods={"POST"})
     *
     * @param SendingDiscussionMessage $message
     * @param Education $education
     * @param Discipline $discipline
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function disciplineChatMessagesAdd(
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
            $isAllowed = $this->disciplineRepository->isUserAllowedToSendMessageToDisciplineChat($user->getDbOid(), $semester->getSemesterId(), $group, $discipline->getDisciplineId());

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
            $msgId = $this->disciplineRepository->addNewDisciplineDiscussionMessage(
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

        return $this->responseSuccessWithObject($addedMessage);
    }

    /**
     * @Route("/chat/attachment", name="discipline-chat-add", methods={"POST"})
     *
     * @param BinaryFile $file
     * @param Message $message
     * @return JsonResponse
     */
    public function disciplineChatAttachmentAdd(BinaryFile $file, Message $message): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $isBelongs = $this->disciplineRepository->isMessageBelongsToUser($message->getMsg(), $user->getDbOid());

            if(!$isBelongs) {
                throw new AccessDeniedException('Message');
            }

            $this->disciplineRepository->addAttachmentToMessage($message->getMsg(), $file);
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccess();
    }

    /**
     * @Route("/materials/list", name="discipline-materials-list", methods={"GET"})
     * @param Discipline $discipline
     * @param Education $education
     * @param Semester $semester
     * @param WithJsonFlag $withJsonFlag
     * @return JsonResponse
     */
    public function disciplineMaterials(
        Discipline $discipline,
        Education $education,
        Semester$semester,
        WithJsonFlag $withJsonFlag
    ): JsonResponse
    {
        try {
            $materials = $this->disciplineRepository->getDisciplineTeachingMaterials(
                $discipline->getDisciplineId(), $education->getEducationId(), $semester->getSemesterId(), $withJsonFlag->getWithJsonData());
        } catch (Exception $e) {
            throw new DataAccessException();
        }

        $listedResponse = new ListedResponse();
        $listedResponse->setCount(count($materials));
        $listedResponse->setPayload($materials);

        return $this->responseSuccessWithObject($listedResponse);
    }

    /**
     * @Route("/materials/content", name="discipline-materials-content", methods={"GET"})
     */
    public function disciplineMaterialsFileDownload()
    {
        $att = $this->disciplineRepository->getTeachingMaterialsAttachment('5:93490744');
        var_dump(gettype($att));
    }

    /**
     * @Route("/tasks", name="discipline-studwork", methods={"GET"})
     *
     * @param Discipline $discipline
     * @param Education $education
     * @param Semester $semester
     * @param EducationRepository $educationRepository
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function disciplineTasks(
        Discipline $discipline,
        Education $education,
        Semester $semester,
        EducationRepository $educationRepository,
        PersonalRepository $personalRepository
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $currentUserEduList = $educationRepository->getUserEducationsIdList($user->getDbOid());
        } catch (Exception $e) {
            throw new DataAccessException();
        }

        if (!in_array($education, array_values($currentUserEduList))) {
            throw new AccessDeniedException('Tasks');
        }

        try {
            $group = $personalRepository->getGroupByContingent($education->getEducationId());
            $workList = $this->disciplineRepository
                ->getStudentWorksList($semester->getSemesterId(),
                    $discipline->getDisciplineId(), $group, $education->getEducationId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $workListAnswer = new ListedResponse();
        $workListAnswer->setCount(count($workList));
        $workListAnswer->setPayload($workList);

        return $this->responseSuccessWithObject($workListAnswer);
    }

    /**
     * @Route("/tasks/answer", name="discipline-studwork-add", methods={"POST"})
     */
    public function disciplineTasksAdd(Request $request): JsonResponse
    {
        // Todo: implement
    }

    /**
     * @Route("/list", name="get_semester_subjects", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Дисциплины"},
     *     summary="Список дисциплин студента в заданном семестре",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          name="edu",
     *          in="query",
     *          description="Идентификатор периода обучения в ЛГТУ",
     *          required=true
     *     ),
     *     @OA\Parameter(
     *          name="sem",
     *          in="query",
     *          description="Идентификатор семестра, для которого выводится список дисциплин",
     *          required=true
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Массив объектов семестра",
     *          @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=Discipline::class, groups={"Default"}))))
     *     )
     * )
     *
     * @param Education $education
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     */
    public function getDisciplineListBySemester(
        Education $education,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());

            $semesterSubjects = $this->disciplineRepository->getDisciplinesBySemester($groupId, $semester->getSemesterId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $disciplineList = new ListedResponse();
        $disciplineList->setCount(count($semesterSubjects));
        $disciplineList->setPayload($semesterSubjects);

        return $this->responseSuccessWithObject($disciplineList);
    }
}