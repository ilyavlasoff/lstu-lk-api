<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\DTO\ListedResponse;
use App\Model\QueryParam\Discipline;
use App\Model\QueryParam\Education;
use App\Model\QueryParam\Semester;
use App\Model\QueryParam\WithJsonFlag;
use App\Repository\TeachingMaterialsRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class TeachingMaterialsController
 * @package App\Controller
 * @Route("/api/v1/materials")
 */
class TeachingMaterialsController extends AbstractRestController
{
    private $teachingMaterialsRepository;

    public function __construct(SerializerInterface $serializer, TeachingMaterialsRepository $teachingMaterialsRepository)
    {
        parent::__construct($serializer);
        $this->teachingMaterialsRepository = $teachingMaterialsRepository;
    }

    /**
     * @Route("/list", name="discipline_materials_list", methods={"GET"})
     * @param Discipline $discipline
     * @param Education $education
     * @param Semester $semester
     * @param WithJsonFlag $withJsonFlag
     * @return JsonResponse
     *
     * @OA\Get(
     *     tags={"Учебные материалы"},
     *     summary="Получение списка учебных материалов",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="dis",
     *          description="Идентификатор дисциплины"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="edu",
     *          description="Идентификатор периода обучения"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="sem",
     *          description="Идентификатор учебного семестра"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Список учебных материалов по запрошенной дисциплине",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\Achievement::class, groups={"Default"})))
     *          ))
     *     )
     * )
     */
    public function getDisciplineMaterialsList(
        Discipline $discipline,
        Education $education,
        Semester$semester,
        WithJsonFlag $withJsonFlag
    ): JsonResponse
    {
        try {
            $materials = $this->teachingMaterialsRepository->getDisciplineTeachingMaterials(
                $discipline->getDisciplineId(), $education->getEducationId(), $semester->getSemesterId(), $withJsonFlag->getWithJsonData());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException();
        }

        $listedResponse = new ListedResponse();
        $listedResponse->setCount(count($materials));
        $listedResponse->setPayload($materials);

        return $this->responseSuccessWithObject($listedResponse);
    }
}