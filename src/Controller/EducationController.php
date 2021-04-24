<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Exception\ResourceNotFoundException;
use App\Model\Response\EducationsList;
use App\Model\Response\SemestersList;
use App\Repository\EducationRepository;
use App\Repository\PersonalRepository;
use App\Service\Validation\EducationValidationService;
use App\Service\Validation\PersonValidationService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class EducationController extends AbstractController
{
    private $educationRepository;
    private $serializer;

    public function __construct(SerializerInterface $serializer, EducationRepository $educationRepository)
    {
        $this->serializer = $serializer;
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
     * @param Request $request
     * @return JsonResponse
     */
    public function educationList(
        Request $request,
        PersonValidationService $personValidationService
    ): JsonResponse {
        $personId = $request->query->get('p');
        $personValidationService->validate($personId, 'p');

        try {
            $educations = $this->educationRepository->getLstuEducationListByPerson($personId);
        } catch (\Exception $e) {
            throw new DataAccessException('Education');
        }

        $educationList = new EducationsList();
        $educationList->setPerson($personId);
        $educationList->setEducations($educations);

        return new JsonResponse(
            $this->serializer->serialize($educationList, 'json'),
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
    public function semesterList(
        Request $request,
        PersonalRepository $personalRepository,
        EducationValidationService $educationValidationService
    ): JsonResponse {
        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        $groupId = $personalRepository->getGroupByContingent($education);
        $semesters = $this->educationRepository->getSemesterList($groupId);

        $semesterList = new SemestersList();
        $semesterList->setEducation($education);
        $semesterList->setCurrent(false);
        $semesterList->setSemesters($semesters);

        return new JsonResponse(
            $this->serializer->serialize($semesterList, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
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
     * @param Request $request
     */
    public function currentSemester(
        Request $request,
        PersonalRepository $personalRepository,
        EducationValidationService $educationValidationService
    ): JsonResponse{
        $education = $request->query->get('edu');
        $educationValidationService->validate($education, 'edu');

        try {
            $groupId = $personalRepository->getGroupByContingent($education);
            $semester = $this->educationRepository->getCurrentSemester($groupId);
        } catch (ResourceNotFoundException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new DataAccessException('Semester', $e);
        }

        return new JsonResponse(
            $this->serializer->serialize($semester, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

}