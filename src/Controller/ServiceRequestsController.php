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
use OpenApi\Annotations as OA;

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
     *
     * @OA\Get(
     *     tags={"Сервис"},
     *     summary="Список участников обсуждения дисциплины",
     *      @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="dis",
     *          description="Идентификатор дисциплины"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="g",
     *          description="Идентификатор группы"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="sem",
     *          description="Идентификатор учебного семестра"
     *     ),
     *     @OA\Response(response="404", description="Не найдено"),
     *     @OA\Response(
     *          response="200",
     *          description="Список идентификаторов пользователей в обсуждении",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(@OA\Property(property="uoid", nullable=false, description="Идентификатор пользователя - участника обсуждения", example="5:3495734")))
     *          ))
     *     )
     * )
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