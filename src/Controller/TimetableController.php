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
     * @Route("", name="student_timetable_get", methods={"GET"})
     *
     * @param \App\Model\QueryParam\Week $week
     * @param Education $education
     * @param Semester $semester
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
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