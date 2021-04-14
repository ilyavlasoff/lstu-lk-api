<?php

namespace App\Controller;

use App\Repository\DisciplineRepository;
use App\Repository\PersonalRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
     * @param Request $request
     */
    public function discipline(Request $request)
    {
        $discipline = $request->query->get('dis');
        $education = $request->query->get('edu');
        $semester = $request->query->get('sem');


    }

    /**
     * @Route("/teachers", name="discipline-teachers", methods={"GET"})
     */
    public function disciplineTeachers(Request $request, PersonalRepository $personalRepository)
    {
        $discipline = $request->query->get('dis');
        $education = $request->query->get('edu');
        $semester = $request->query->get('sem');

        try {
            $group = $personalRepository->getGroupByContingent($education);
            $teachersDiscipline = $this->disciplineRepository->getTeachersByDiscipline($discipline, $group, $semester);
            return $this->responseSuccessWithObject($teachersDiscipline);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @Route("/timetable", name="discipline-timetable", methods={"GET"})
     */
    public function disciplineTimetable(Request $request)
    {

    }

    /**
     * @Route("/chat", name="discipline-chat", methods={"GET"})
     */
    public function disciplineChatMessages(Request $request)
    {

    }

    /**
     * @Route("/chat", name="discipline-chat-add", methods={"POST"})
     */
    public function disciplineChatMessagesAdd(Request $request)
    {

    }

    /**
     * @Route("/studwork", name="discipline-studwork", methods={"GET"})
     */
    public function disciplineStudworks(Request $request): JsonResponse
    {

    }

    /**
     * @Route("/studwork/answer", name="discipline-studwork-add", methods={"POST"})
     */
    public function disciplineStudworkAdd(Request $request): JsonResponse
    {

    }
}