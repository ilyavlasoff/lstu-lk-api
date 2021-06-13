<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\DTO\Day;
use App\Model\DTO\Week;
use App\Model\DTO\TimetableItem;
use App\Model\QueryParam\Education;
use App\Model\QueryParam\Semester;
use App\Model\DTO\ListedResponse;
use App\Model\DTO\Timetable;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use App\Model\DTO\Exam;
use Nelmio\ApiDocBundle\Annotation\Security;

/**
 * Class TimetableController
 * @package App\Controller
 * @Route("/api/v1/student/timetable")
 */
class TimetableController extends AbstractRestController
{
    private $timetableRepository;

    public function __construct(SerializerInterface $serializer, TimetableRepository $timetableRepository)
    {
        parent::__construct($serializer);
        $this->timetableRepository = $timetableRepository;
    }

    /**
     * @Route("", name="student_timetable_get", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Расписание"},
     *     summary="Учебное расписание",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="week",
     *          description="Наименование типа недели"
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
     *          description="Учебное расписание на запрошенную неделю",
     *          @OA\JsonContent(
     *              ref=@Model(type=Timetable::class)
     *          )
     *     )
     * )
     *
     * @param \App\Model\QueryParam\Week $week
     * @param Education $education
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     *
     *
     */
    public function lessonTimetable(
        \App\Model\QueryParam\Week $week,
        Education $education,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse
    {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());

            if($week->getWeekCode()) {
                $weekCode = $this->timetableRepository->getWeekByName($week->getWeekNameValue());
                $timetable = $this->timetableRepository->getTimetable($groupId, $semester->getSemesterId(), $weekCode);
            } else {
                $timetable = $this->timetableRepository->getTimetable($groupId, $semester->getSemesterId());
            }

        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException();
        }

        return $this->responseSuccessWithObject($timetable);
    }

    /**
     * @Route("/exams/list", name="exams_timetable", methods={"GET"})
     *
     * @param Education $education
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws DataAccessException
     * @throws \Exception
     *
     * @OA\Get(
     *     tags={"Расписание"},
     *     summary="Расписание экзаменов",
     *     @Security(name="Bearer"),
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
     *          description="Cписок экзаменов, запларированных в данном семестре",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\Exam::class, groups={"Default"})))
     *          ))
     *     )
     * )
     */
    public function examsTimetable(
        Education $education,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());
            $exams = $this->timetableRepository->getExamsTimetable($groupId, $semester->getSemesterId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $examsTimetable = new ListedResponse();
        $examsTimetable->setCount(count($exams));
        $examsTimetable->setPayload($exams);

        return $this->responseSuccessWithObject($examsTimetable);
    }
}