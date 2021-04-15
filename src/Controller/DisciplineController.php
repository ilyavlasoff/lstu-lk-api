<?php

namespace App\Controller;

use App\Document\User;
use App\Model\Grouping\Day;
use App\Model\Grouping\Week;
use App\Model\Mapping\TimetableItem;
use App\Model\Response\DiscussionChatList;
use App\Model\Response\Timetable;
use App\Repository\DisciplineRepository;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use App\Repository\TimetableRepository;
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
    public function disciplineTimetable(
        Request $request,
        TimetableRepository $timetableRepository,
        PersonalRepository $personalRepository
    ) {
        $discipline = $request->query->get('dis');
        $education = $request->query->get('edu');
        $semester = $request->query->get('sem');

        $group = $personalRepository->getGroupByContingent($education);

        $timetableForDiscipline = $timetableRepository->getTimetable($group, $semester, null, $discipline);

        return $this->responseSuccessWithObject($timetableForDiscipline);
    }

    /**
     * @Route("/chat", name="discipline-chat", methods={"GET"})
     */
    public function disciplineChatMessages(
        Request $request,
        EducationRepository $educationRepository,
        PersonalRepository $personalRepository
    ): JsonResponse {
        $discipline = $request->query->get('dis');
        $education = $request->query->get('edu');
        $semester = $request->query->get('sem');
        $offset = $request->query->get('of');
        $count = $request->query->get('c');

        /** @var User $user */
        $user = $this->getUser();
        $currentUserEduList = $educationRepository->getUserEducationsIdList($user->getDbOid());
        if(!($education && in_array($education, array_values($currentUserEduList)))) {
            throw new \Exception('Operation not allowed for this user');
        }

        try {
            $group = $personalRepository->getGroupByContingent($education);

            $disciplineChatMessages = $this->disciplineRepository->getDisciplineChatMessages($semester, $discipline, $group, $offset, $count);

            $totalMessageCount = $this->disciplineRepository->getDisciplineChatMessagesCount($group, $semester, $discipline);
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
            return $this->responseSuccessWithObject($discussionChatList);

        } catch (\Exception $e) {
            throw $e;
        }
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