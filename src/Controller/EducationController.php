<?php

namespace App\Controller;

use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
     *          name="person",
     *          description="Идентификатор пользователя"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Массив объектов обучения",
     *          @OA\JsonContent(type="array", @OA\Items(ref=@Model(type=Education::class, groups={"Default"})))
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function getEducationList(Request $request): JsonResponse
    {
        $personId = $request->query->get('person');

        $educationList = $this->educationRepository->getLstuEducationListByPerson($personId);

        return new JsonResponse(
            $this->serializer->serialize($educationList, 'json',
                SerializationContext::create()->setInitialType('array<App\Model\Mapping\Education>')
            ),
            Response::HTTP_OK,
            [],
            true
        );
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
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function getSemesterList(Request $request, PersonalRepository $personalRepository)
    {
        $education = $request->query->get('edu');

        $groupId = $personalRepository->getGroupByContingent($education);
        $semesterList = $this->educationRepository->getSemesterList($groupId);
        return new JsonResponse(
            $this->serializer->serialize($semesterList, 'json',
                SerializationContext::create()->setInitialType('array<App\Model\Mapping\Semester>')
            ),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/", name="get_education_details", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Образование"},
     *     summary="Расширенная информация о запрошенном периоде обучения",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     )
     * )
     * @param Request $request
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     */
    public function getEducationDetails(Request $request, PersonalRepository $personalRepository): JsonResponse
    {
        $education = $request->query->get('edu');

    }
}