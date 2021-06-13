<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\DataAccessException;
use App\Model\DTO\Group;
use App\Model\DTO\Semester;
use App\Model\QueryParam\Person;
use App\Model\DTO\ListedResponse;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use Doctrine\DBAL\Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Model\DTO\Education;
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
     * @Route("/list", name="educations_list", methods={"GET"})
     * @OA\Get(
     *     tags={"Образование"},
     *     summary="Список периодов обучения студента",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="p",
     *          description="Идентификатор пользователя"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Лист списка объектов достижений пользователя c данными о пагинации",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="offset", type="integer"),
     *              @OA\Property(property="next_offset", type="integer"),
     *              @OA\Property(property="remains", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\Education::class, groups={"Default"})))
     *          ))
     *     )
     * )
     * @param Person $person
     * @return JsonResponse
     * @throws DataAccessException
     */
    public function getEducationList(Person $person): JsonResponse
    {
        try {
            $educations = $this->educationRepository->getLstuEducationListByPerson($person->getPersonId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception | \Exception $e) {
            throw new DataAccessException($e);
        }

        $educationList = new ListedResponse();
        $educationList->setCount(count($educations));
        $educationList->setPayload($educations);

        return $this->responseSuccessWithObject($educationList);
    }

    /**
     * @Route("/semesters/list", name="semesters_list", methods={"GET"})
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
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\Semester::class, groups={"Default"})))
     *          ))
     *     )
     * )
     * @param \App\Model\QueryParam\Education $education
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function getSemesterList(\App\Model\QueryParam\Education $education, PersonalRepository $personalRepository): JsonResponse
    {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());
            $semesters = $this->educationRepository->getSemesterList($groupId);
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $semesterList = new ListedResponse();
        $semesterList->setCount(count($semesters));
        $semesterList->setPayload($semesters);

        return $this->responseSuccessWithObject($semesterList);
    }

    /**
     * @Route("/semesters/current", name="current_semester", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Образование"},
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
     * @param \App\Model\QueryParam\Education $education
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws DataAccessException
     */
    public function getCurrentSemester(\App\Model\QueryParam\Education $education, PersonalRepository $personalRepository): JsonResponse
    {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());
            $semester = $this->educationRepository->getCurrentSemester($groupId);
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($semester);
    }

    /**
     * @Route("/groups/ids/list", name="groups_ids_full_list", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Образование"},
     *     summary="Список идентификаторов групп пользователя",
     *     @Security(name="Bearer"),
     *     @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(@OA\Property(type="string", description="Идентификатор группы пользователя", property="id", example="5:24534546"))))
     *          ))
     * )
     * @return JsonResponse
     */
    public function getGroupIdentifiersList() {
        /** @var User $user */
        $user = $this->getUser();

        try {
            /** @var Group[] $groups */
            $groups = $this->educationRepository->getUserGroupsIdList($user->getDbOid());
        } catch (\Doctrine\DBAL\Driver\Exception | Exception $e) {
            throw new DataAccessException($e);
        }

        $response = new ListedResponse();
        $response->setOffset(0);
        $response->setRemains(0);
        $response->setCount(count($groups));
        $response->setPayload($groups);

        return $this->responseSuccessWithObject($response);
    }

}