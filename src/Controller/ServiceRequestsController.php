<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\DTO\ListedResponse;
use App\Model\QueryParam\Discipline;
use App\Model\QueryParam\Group;
use App\Model\QueryParam\Semester;
use App\Repository\DisciplineDiscussionRepository;
use Doctrine\DBAL\Driver\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ServiceRequestsController
 * @package App\Controller
 * @Route("/api/v1/service")
 */
class ServiceRequestsController extends AbstractRestController
{
    /**
     * @Route("/discussion/members/list", name="discipline_discussion_members_list", methods={"GET"})
     *
     * @param Discipline $discipline
     * @param Semester $semester
     * @param Group $group
     * @param DisciplineDiscussionRepository $disciplineDiscussionRepository
     * @return JsonResponse
     * @throws Exception
     */
    public function getDiscussionChatMemberIds(
        Discipline $discipline,
        Semester $semester,
        Group $group,
        DisciplineDiscussionRepository $disciplineDiscussionRepository
    ): JsonResponse
    {
        try {
            $chatMembers = $disciplineDiscussionRepository->getChatMembersIds(
                $semester->getSemesterId(), $group->getGroupId(), $discipline->getDisciplineId());
        } catch (\Doctrine\DBAL\Exception | \Exception $e) {
            throw new DataAccessException($e);
        }

        $membersList = new ListedResponse();
        $membersList->setCount(count($chatMembers));
        $membersList->setPayload($chatMembers);

        return $this->responseSuccessWithObject($membersList);
    }
}