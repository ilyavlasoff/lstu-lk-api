<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\Mapping\Semester;
use App\Model\Request\Person;
use App\Model\Response\EducationsList;
use App\Model\Response\SemestersList;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use Doctrine\DBAL\Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     *          name="p",
     *          description="Идентификатор пользователя"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Массив объектов обучения",
     *          @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=Education::class, groups={"Default"})))
     *     )
     * )
     * @param \App\Model\Request\Person $person
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     */
    public function educationList(Person $person): JsonResponse
    {
        try {
            $educations = $this->educationRepository->getLstuEducationListByPerson($person->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $educationList = new EducationsList();
        $educationList->setPerson($person->getPersonId());
        $educationList->setEducations($educations);

        return $this->responseSuccessWithObject($educationList);
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
     * @param \App\Model\Request\Education $education
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function semesterList(\App\Model\Request\Education $education, PersonalRepository $personalRepository): JsonResponse
    {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());
            $semesters = $this->educationRepository->getSemesterList($groupId);
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $semesterList = new SemestersList();
        $semesterList->setEducation($education->getEducationId());
        $semesterList->setCurrent(false);
        $semesterList->setSemesters($semesters);

        return $this->responseSuccessWithObject($semesterList);
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
     * @param \App\Model\Request\Education $education
     * @param \App\Repository\PersonalRepository $personalRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \App\Exception\DataAccessException
     */
    public function currentSemester(\App\Model\Request\Education $education, PersonalRepository $personalRepository): JsonResponse
    {
        try {
            $groupId = $personalRepository->getGroupByContingent($education->getEducationId());
            $semester = $this->educationRepository->getCurrentSemester($groupId);
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($semester);
    }

}