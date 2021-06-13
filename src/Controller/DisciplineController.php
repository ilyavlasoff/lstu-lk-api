<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\AccessDeniedException;
use App\Exception\DataAccessException;
use App\Model\DTO\Day;
use App\Model\DTO\Week;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\DiscussionMessage;
use App\Model\DTO\TimetableItem;
use App\Model\QueryParam\Discipline;
use App\Model\QueryParam\Education;
use App\Model\QueryParam\DisciplineDiscussionMessage;
use App\Model\QueryParam\Paginator;
use App\Model\QueryParam\Semester;
use App\Model\QueryParam\SendingDiscussionMessage;
use App\Model\QueryParam\WithJsonFlag;
use App\Model\DTO\ListedResponse;
use App\Model\DTO\Timetable;
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
     * @Route("", name="discipline_get", methods={"GET"})
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
     *     @OA\Response(
     *          response="200",
     *          description="Объект учебной дисциплины",
     *          @OA\JsonContent(ref=@Model(type=App\Model\DTO\Discipline::class, groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Некорректные параметры вызова"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Запрошенное значение не найдено"
     *     )
     * )
     * @param Discipline $discipline
     * @return JsonResponse
     */
    public function discipline(Discipline $discipline): JsonResponse
    {
        try {
            $discipline = $this->disciplineRepository->getDiscipline($discipline->getDisciplineId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($discipline);
    }

    /**
     * @Route("/teachers", name="discipline_teachers_list", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Дисциплины"},
     *     summary="Информация о преподавателях дисциплины",
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
     *     @OA\Response(
     *          response="200",
     *          description="Список преподавателей по дисциплине",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\TimetableItem::class, groups={"Default"})))
     *          ))
     *     ),
     * )
     *
     * @param Discipline $discipline
     * @param Education $education
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
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
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $disciplineTeachersList = new ListedResponse();
        $disciplineTeachersList->setCount(count($teachersDiscipline));
        $disciplineTeachersList->setPayload($teachersDiscipline);

        return $this->responseSuccessWithObject($disciplineTeachersList);
    }

    /**
     * @Route("/timetable", name="discipline_timetable_get", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Дисциплины"},
     *     summary="Расписание выборанной дисциплины",
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
     *     @OA\Response(
     *          response="200",
     *          description="Успешно получена информация о расписании дисциплин",
     *          @OA\JsonContent(ref=@Model(type=Timetable::class)))
     *     )
     *
     * @param Discipline $discipline
     * @param Education $education
     * @param Semester $semester
     * @param TimetableRepository $timetableRepository
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
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
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($timetableForDiscipline);
    }

    /**
     * @Route("/list", name="semester_discipline_list", methods={"GET"})
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
     * @throws DataAccessException
     */
    public function getDisciplineListBySemester(
        Education $education,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());

            $semesterSubjects = $this->disciplineRepository->getDisciplinesBySemester($groupId, $semester->getSemesterId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $disciplineList = new ListedResponse();
        $disciplineList->setCount(count($semesterSubjects));
        $disciplineList->setPayload($semesterSubjects);

        return $this->responseSuccessWithObject($disciplineList);
    }
}