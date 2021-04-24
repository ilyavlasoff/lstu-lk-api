<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\DataAccessException;
use App\Exception\PermissionsException;
use App\Exception\ResourceNotFoundException;
use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Mapping\TimetableItem;
use App\Model\Response\DiscussionChatList;
use App\Model\Response\StudentWorkList;
use App\Model\Response\Timetable;
use App\Repository\DisciplineRepository;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
use App\Service\Validation\DisciplineValidationService;
use App\Service\Validation\EducationValidationService;
use App\Service\Validation\PaginationValidationService;
use App\Service\Validation\SemesterValidationService;
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
class DisciplineController extends AbstractController
{
    private $disciplineRepository;
    private $serializer;

    public function __construct(SerializerInterface $serializer, DisciplineRepository $disciplineRepository)
    {
        $this->serializer = $serializer;
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
     * @param Request $request
     */
    public function discipline(
        Request $request,
        DisciplineValidationService $disciplineValidationService,
        EducationValidationService $educationValidationService,
        SemesterValidationService $semesterValidationService
    ): JsonResponse {
        $discipline = $request->query->get('dis');
        $disciplineValidationService->validate($discipline, 'dis');

        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $semester = $request->query->get('sem');
        $semesterValidationService->validate($semester, 'sem');


    }

    /**
     * @Route("/teachers", name="discipline-teachers", methods={"GET"})
     */
    public function disciplineTeachers(
        Request $request,
        PersonalRepository $personalRepository,
        DisciplineValidationService $disciplineValidationService,
        EducationValidationService $educationValidationService,
        SemesterValidationService $semesterValidationService
    ): JsonResponse {
        $discipline = $request->query->get('dis');
        $disciplineValidationService->validate($discipline, 'dis');

        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $semester = $request->query->get('sem');
        $semesterValidationService->validate($semester, 'sem');

        try {
            $group = $personalRepository->getGroupByContingent($education);
            $teachersDiscipline = $this->disciplineRepository->getTeachersByDiscipline($discipline, $group, $semester);
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DataAccessException('Teacher');
        }

        return new JsonResponse(
            $this->serializer->serialize($teachersDiscipline, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/timetable", name="discipline-timetable", methods={"GET"})
     */
    public function disciplineTimetable(
        Request $request,
        TimetableRepository $timetableRepository,
        PersonalRepository $personalRepository,
        DisciplineValidationService $disciplineValidationService,
        EducationValidationService $educationValidationService,
        SemesterValidationService $semesterValidationService
    ) {
        $discipline = $request->query->get('dis');
        $disciplineValidationService->validate($discipline, 'dis');

        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $semester = $request->query->get('sem');
        $semesterValidationService->validate($semester, 'sem');

        try {
            $group = $personalRepository->getGroupByContingent($education);
            $timetableForDiscipline = $timetableRepository->getTimetable($group, $semester, null, $discipline);
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DataAccessException('Timetable');
        }

        return new JsonResponse(
            $this->serializer->serialize($timetableForDiscipline, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/chat", name="discipline-chat", methods={"GET"})
     */
    public function disciplineChatMessages(
        Request $request,
        EducationRepository $educationRepository,
        PersonalRepository $personalRepository,
        PaginationValidationService $paginationValidationService,
        DisciplineValidationService $disciplineValidationService,
        EducationValidationService $educationValidationService,
        SemesterValidationService $semesterValidationService
    ): JsonResponse {
        $discipline = $request->query->get('dis');
        $disciplineValidationService->validate($discipline, 'dis');

        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $semester = $request->query->get('sem');
        $semesterValidationService->validate($semester, 'sem');

        $offset = $request->query->get('of');
        $count = $request->query->get('c');
        $paginationValidationService->validate($offset, $count, 'of', 'c',true);

        /** @var User $user */
        $user = $this->getUser();
        $currentUserEduList = $educationRepository->getUserEducationsIdList($user->getDbOid());
        if(!($education && in_array($education, array_values($currentUserEduList)))) {
            throw new PermissionsException('Education', $education);
        }

        try {
            $group = $personalRepository->getGroupByContingent($education);
            $disciplineChatMessages = $this->disciplineRepository->getDisciplineChatMessages($semester, $discipline, $group, $offset, $count);
            $totalMessageCount = $this->disciplineRepository->getDisciplineChatMessagesCount($group, $semester, $discipline);
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DataAccessException('Group', $e);
        }

        $remainsCount = $totalMessageCount - $offset - count($disciplineChatMessages);

        $discussionChatList = new DiscussionChatList();
        $discussionChatList->setEducation($education);
        $discussionChatList->setDiscipline($discipline);
        $discussionChatList->setSemester($semester);
        $discussionChatList->setOffset($offset);
        $discussionChatList->setRemains($remainsCount);

        if($remainsCount > 0) {
            $discussionChatList->setNextOffset($offset + count($disciplineChatMessages));
        }

        $discussionChatList->setMessages($disciplineChatMessages);

        return new JsonResponse(
            $this->serializer->serialize($discussionChatList, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/chat/add", name="discipline-chat-add", methods={"POST"})
     */
    public function disciplineChatMessagesAdd(Request $request)
    {

    }

    /**
     * @Route("/tasks", name="discipline-studwork", methods={"GET"})
     */
    public function disciplineTasks(
        Request $request,
        EducationRepository $educationRepository,
        PersonalRepository $personalRepository,
        DisciplineValidationService $disciplineValidationService,
        EducationValidationService $educationValidationService,
        SemesterValidationService $semesterValidationService
    ): JsonResponse {
        $discipline = $request->query->get('dis');
        $disciplineValidationService->validate($discipline, 'dis');

        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $semester = $request->query->get('sem');
        $semesterValidationService->validate($semester, 'sem');

        /** @var User $user */
        $user = $this->getUser();
        $currentUserEduList = $educationRepository->getUserEducationsIdList($user->getDbOid());
        if(!($education && in_array($education, array_values($currentUserEduList)))) {
            throw new PermissionsException('Education', $education);
        }

        try {
            $group = $personalRepository->getGroupByContingent($education);
            $workList = $this->disciplineRepository->getStudentWorksList($semester, $discipline, $group, $education);
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DataAccessException('Studwork');
        }

        $workListAnswer = new StudentWorkList();
        $workListAnswer->setSemester($semester);
        $workListAnswer->setDiscipline($discipline);
        $workListAnswer->setEducation($education);
        $workListAnswer->setTasks($workList);

        return new JsonResponse(
            $this->serializer->serialize($workListAnswer, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/tasks/answer", name="discipline-studwork-add", methods={"POST"})
     */
    public function disciplineTasksAdd(Request $request): JsonResponse
    {

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
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function getSubjectListBySemester(Request $request, PersonalRepository $personalRepository): JsonResponse
    {
        $educationId = $request->query->get('edu');
        $semesterId = $request->query->get('sem');

        try {
            $groupId = $personalRepository->getGroupByContingent($educationId);

            if(!$groupId) {
                throw new \Exception('Invalid group');
            }

        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $semesterSubjects = $this->subjectRepository->getSubjectsBySemester($groupId, $semesterId);
        } catch (\Exception $e) {
            throw $e;
        }

        return new JsonResponse(
            $this->serializer->serialize(
                $semesterSubjects,
                'json',
                SerializationContext::create()->setInitialType('array<App\Model\Mapping\AcademicSubject>')
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }
}