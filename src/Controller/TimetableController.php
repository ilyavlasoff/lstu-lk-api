<?php

namespace App\Controller;

use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Response\ExamsTimetable;
use App\Model\Response\Timetable;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
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
     * @Route("/", name="lesson_timetable", methods={"GET"})
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

        if(!$education || !$semester) {
            throw new \Exception('Bad request');
        }

        try {
            $groupId = $personalRepository->getGroupByContingent($education);
        } catch (\Exception $e) {
            throw $e;
        }

        $timetable = new Timetable();
        $timetable->setGroupId($groupId);
        $timetable->setGroupName('group');

        $timetableWeekTranslate = [
            'green' => 'Зеленая',
            'white' => 'Белая',
        ];

        if($week) {
            if(!array_key_exists($week, $timetableWeekTranslate)) {
                throw new \Exception('Incorrect query');
            }
            $timetableWeekNames = [$timetableWeekTranslate[$week]];
        } else {
            $timetableWeekNames = array_values($timetableWeekTranslate);
        }

        $studyWeeks = [];

        foreach ($timetableWeekNames as $weekName) {
            $weekCode = $this->timetableRepository->getWeekByName($weekName);
            $days = $this->timetableRepository->getDays();

            $timetableWeek = new Week();
            $timetableWeek->setType($week);

            $timetableItems = $this->timetableRepository->getTimetable($groupId, $semester, $weekCode);

            foreach ($timetableItems as $weekTimetable) {
                $weekDays = [];

                foreach ($weekTimetable as $day => $dayTimetable) {

                    /** @var Day[] $currentDay */
                    $currentDay = array_values(
                        array_filter($days,
                            function (Day $fday) use($day) {
                                return $fday->getId() === $day;
                            }
                        )
                    );

                    if(count($currentDay)) {
                        $currentDay[0]->setLessons($dayTimetable);
                        $weekDays[] = $currentDay[0];
                    }

                }
                $timetableWeek->setDays($weekDays);
            }

            $studyWeeks[] = $week;
        }

        $timetable->setWeeks($studyWeeks);
        return $this->responseSuccessWithObject($timetable);
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
            $exams = $this->timetableRepository->getExamsTimetable($groupId, $semester);

            $examsTimetable = new ExamsTimetable();
            $examsTimetable->setEdu($education);
            $examsTimetable->setSem($semester);
            $examsTimetable->setExams($exams);

            return $this->responseSuccessWithObject($examsTimetable);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}