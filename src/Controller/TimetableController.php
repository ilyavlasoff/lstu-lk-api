<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Mapping\TimetableItem;
use App\Model\Request\Education;
use App\Model\Request\Semester;
use App\Model\Response\ListedResponse;
use App\Model\Response\Timetable;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     */
    public function lessonTimetable(
        \App\Model\Request\Week $week,
        Education $education,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse
    {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());

            if($week) {
                $weekCode = $this->timetableRepository->getWeekByName($week->getWeekName());
                $timetable = $this->timetableRepository->getTimetable($groupId, $semester->getSemesterId(), $weekCode);
            } else {
                $timetable = $this->timetableRepository->getTimetable($groupId, $semester->getSemesterId());
            }

        } catch (Exception $e) {
            throw new DataAccessException();
        }

        return $this->responseSuccessWithObject($timetable);
    }

    /**
     * @Route("/exams", name="exams_timetable", methods={"GET"})
     *
     * @param \App\Model\Request\Education $education
     * @param \App\Model\Request\Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     */
    public function examsTimetable(
        Education $education,
        Semester $semester,
        PersonalRepository $personalRepository
    ): JsonResponse {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());
            $exams = $this->timetableRepository->getExamsTimetable($groupId, $semester->getSemesterId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $examsTimetable = new ListedResponse();
        $examsTimetable->setCount(count($exams));
        $examsTimetable->setPayload($exams);

        return $this->responseSuccessWithObject($examsTimetable);
    }
}