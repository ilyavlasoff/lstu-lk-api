<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Mapping\AcademicSubject;
use App\Model\Mapping\TimetableItem;
use App\Model\Request\Discipline;
use App\Model\Request\Education;
use App\Model\Request\Paginator;
use App\Model\Request\Semester;
use App\Model\Response\DiscussionChatList;
use App\Model\Response\StudentWorkList;
use App\Model\Response\Timetable;
use App\Repository\DisciplineRepository;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
use Doctrine\DBAL\Exception;
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
     * @param \App\Model\Request\Discipline $discipline
     * @param \App\Model\Request\Education $education
     * @param \App\Model\Request\Semester $semester
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function discipline(Discipline $discipline, Education $education, Semester $semester): JsonResponse
    {
        // TODO: Needs to implement

        return $this->responseSuccessWithObject([]);
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

        return $this->responseSuccessWithObject($teachersDiscipline);
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

        $discussionChatList = new DiscussionChatList();
        $discussionChatList->setEducation($education->getEducationId());
        $discussionChatList->setDiscipline($discipline->getDisciplineId());
        $discussionChatList->setSemester($semester->getSemesterId());
        $discussionChatList->setOffset($paginator->getOffset());
        $discussionChatList->setRemains($remainsCount);

        if($remainsCount > 0) {
            $discussionChatList->setNextOffset($paginator->getOffset() + count($disciplineChatMessages));
        }

        $discussionChatList->setMessages($disciplineChatMessages);

        return $this->responseSuccessWithObject($discussionChatList);
    }

    /**
     * @Route("/chat/add", name="discipline-chat-add", methods={"POST"})
     */
    public function disciplineChatMessagesAdd(Request $request)
    {
        // TODO: implement
    }

    /**
     * @Route("/tasks", name="discipline-studwork", methods={"GET"})
     *
     * @param \App\Model\Request\Discipline $discipline
     * @param \App\Model\Request\Education $education
     * @param \App\Model\Request\Semester $semester
     * @param \App\Repository\EducationRepository $educationRepository
     * @param \App\Repository\PersonalRepository $personalRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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

        $workListAnswer = new StudentWorkList();
        $workListAnswer->setSemester($semester->getSemesterId());
        $workListAnswer->setDiscipline($discipline->getDisciplineId());
        $workListAnswer->setEducation($education->getEducationId());
        $workListAnswer->setTasks($workList);

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
     *          @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=AcademicSubject::class, groups={"Default"}))))
     *     )
     * )
     *
     * @param \App\Model\Request\Education $education
     * @param \App\Model\Request\Semester $semester
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

        return $this->responseSuccessWithObject($semesterSubjects);
    }
}