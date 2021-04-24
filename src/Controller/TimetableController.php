<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Exception\ResourceNotFoundException;
use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Mapping\TimetableItem;
use App\Model\Response\ExamsTimetable;
use App\Model\Response\Timetable;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
use App\Service\Validation\EducationValidationService;
use App\Service\Validation\SemesterValidationService;
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
class TimetableController extends AbstractController
{
    private $serializer;
    private $timetableRepository;

    public function __construct(SerializerInterface $serializer, TimetableRepository $timetableRepository)
    {
        $this->serializer = $serializer;
        $this->timetableRepository = $timetableRepository;
    }

    /**
     * @Route("/", name="lesson_timetable", methods={"GET"})
     */
    public function lessonTimetable(
        Request $request,
        PersonalRepository $personalRepository,
        EducationValidationService $educationValidationService,
        SemesterValidationService $semesterValidationService
    ): JsonResponse {
        $week = $request->query->get('week');

        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $semester = $request->query->get('sem');
        $semesterValidationService->validate($semester, 'sem');

        $timetableWeekTranslate = [
            'green' => 'Зеленая',
            'white' => 'Белая',
        ];

        try {
            $groupId = $personalRepository->getGroupByContingent($education);

            if($week) {
                $weekCode = $this->timetableRepository->getWeekByName($timetableWeekTranslate[$week]);
                $timetable = $this->timetableRepository->getTimetable($groupId, $semester, $weekCode);
            } else {
                $timetable = $this->timetableRepository->getTimetable($groupId, $semester);
            }

        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DataAccessException('Group');
        }

        return new JsonResponse(
            $this->serializer->serialize($timetable, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/exams", name="exams_timetable", methods={"GET"})
     *
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function examsTimetable(
        Request $request,
        PersonalRepository $personalRepository,
        EducationValidationService $educationValidationService,
        SemesterValidationService $semesterValidationService
    ): JsonResponse {
        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $semester = $request->query->get('sem');
        $semesterValidationService->validate($semester, 'sem');

        try {
            $groupId = $personalRepository->getGroupByContingent($education);
            $exams = $this->timetableRepository->getExamsTimetable($groupId, $semester);

        } catch (\Exception $e) {
            throw new DataAccessException('Exams');
        }

        $examsTimetable = new ExamsTimetable();
        $examsTimetable->setEdu($education);
        $examsTimetable->setSem($semester);
        $examsTimetable->setExams($exams);

        return new JsonResponse(
            $this->serializer->serialize($examsTimetable, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }
}