<?php

namespace App\Controller;

use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Response\Timetable;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\Mapping\Education;
use OpenApi\Annotations as OA;

/**
 * Class EducationController
 * @package App\Controller
 * @Route("/api/v1/student/edu")
 */
class EducationController extends AbstractRestController
{
    private $educationRepository;

    public function __construct(SerializerInterface $serializer, EducationRepository $educationRepository)
    {
        parent::__construct($serializer);
        $this->educationRepository = $educationRepository;
    }

    /**
     * @Route("/list", name="get_educations_list", methods={"GET"})
     * @OA\Get(
     *     tags={"Образование"},
     *     summary="Список периодов обучения студента",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="person",
     *          description="Идентификатор пользователя"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Массив объектов обучения",
     *          @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=Education::class, groups={"Default"})))
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function educationList(Request $request): JsonResponse
    {
        $personId = $request->query->get('person');

        $educationList = $this->educationRepository->getLstuEducationListByPerson($personId);

        return new JsonResponse(
            $this->serializer->serialize($educationList, 'json',
                SerializationContext::create()->setInitialType('array<App\Model\Mapping\Education>')
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/semesters", name="get_semesters_list", methods={"GET"})
     * @OA\Get(
     *     tags={"Образование"},
     *     summary="Список семестров указанного периода обучения",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Массив объектов семестров",
     *          @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=Semester::class, groups={"Default"})))
     *     )
     * )
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function semesterList(Request $request, PersonalRepository $personalRepository)
    {
        $education = $request->query->get('edu');

        $groupId = $personalRepository->getGroupByContingent($education);
        $semesterList = $this->educationRepository->getSemesterList($groupId);
        return new JsonResponse(
            $this->serializer->serialize($semesterList, 'json',
                SerializationContext::create()->setInitialType('array<App\Model\Mapping\Semester>')
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/semesters/current", name="current_semester", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Обарзование"},
     *     summary="Текущий семестр",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Идентификатор запрошенного семестра",
     *          @OA\JsonContent(ref=@Model(type=Semester::class, groups={"idOnly"}))
     *     )
     * )
     *
     * @param Request $request
     */
    public function currentSemester(Request $request, PersonalRepository $personalRepository)
    {
        $education = $request->query->get('edu');

        if(!$education) {
            throw new \Exception('Bad arguments');
        }

        try {
            $groupId = $personalRepository->getGroupByContingent($education);
            $semester = $this->educationRepository->getCurrentSemester($groupId);
            return $this->responseSuccessWithObject($semester);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @Route("/timetable", name="lesson_timetable", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Расписание"},
     *     summary="Расписание",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="week",
     *          description="Наименование учбеной недели (green-зеленая, white-белая, при отсутствии-обе)"
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
     *
     * )
     *
     * @param Request $request
     * @throws \Exception
     */
    public function lessonTimetable(Request $request, PersonalRepository $personalRepository)
    {
        $week = $request->query->get('week');
        $education = $request->query->get('edu');
        $semester = $request->query->get('sem');

        if(!$education || !$semester || ($week && !in_array($week, ['green', 'white']))) {
            throw new \Exception('Bad response');
        }
        $week = $week ?? ['green', 'white'];

        try {
            $groupId = $personalRepository->getGroupByContingent($education);
        } catch (\Exception $e) {
            throw $e;
        }

        $timetable = new Timetable();
        $timetable->setGroupId($groupId);
        $timetable->setGroupName('group');

        $timetableWeeks = [];
        foreach ($week as $weekName) {
            $week = new Week();
            $week->setType($weekName);

            $dbWeekCodes = $this->educationRepository->getWeeksByName($weekName);
            $days = $this->educationRepository->getDays();

            $timetableItems = $this->educationRepository->getTimetableItems($groupId, $semester, $dbWeekCodes);
            foreach ($timetableItems as $weekTimetable) {
                foreach ($weekTimetable as $day => $dayTimetable) {

                    /** @var Day[] $currentDay */
                    $currentDay = array_filter($days, function (Day $fday) use($day) {return $fday->getId() === $day;});

                    if(count($currentDay)) {
                        $currentDay[0]->setLessons($dayTimetable);
                        $week[] = $currentDay[0];
                    }

                }
            }

            $timetableWeeks[] = $week;
        }

        $timetable->setWeeks($timetableWeeks);
        return $this->responseSuccessWithObject($timetable);
    }

    /**
     * @Route("/", name="get_education_details", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Образование"},
     *     summary="Расширенная информация о запрошенном периоде обучения",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     )
     * )
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function educationDetails(Request $request, PersonalRepository $personalRepository): JsonResponse
    {
        $education = $request->query->get('edu');

    }

    /**
     * @Route("/exams", name="exams_timetable", methods={"GET"})
     *
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function examsTimetable(Request $request, PersonalRepository $personalRepository)
    {
        $education = $request->query->get('edu');
        $semester = $request->query->get('sem');

        try {
            $groupId = $personalRepository->getGroupByContingent($education);
            $exams = $this->educationRepository->getExamsTimetable($groupId, $semester);

            return $this->responseSuccessWithObject($exams);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}