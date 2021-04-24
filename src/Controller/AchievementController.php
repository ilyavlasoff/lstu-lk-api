<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\Mapping\Achievement;
use App\Model\Mapping\Publication;
use App\Model\Response\AchievementList;
use App\Model\Response\AchievementSummary;
use App\Model\Response\PublicationList;
use App\Repository\AchievementRepository;
use App\Service\Validation\PaginationValidationService;
use App\Service\Validation\PersonValidationService;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Nelmio\ApiDocBundle\Annotation\Security;

/**
 * Class AchievementController
 * @package App\Controller
 * @Route("/api/v1/person")
 */
class AchievementController extends AbstractController
{
    private $achievementsRepository;
    private $serializer;

    public function __construct(
        SerializerInterface $serializer,
        AchievementRepository $achievementRepository,
        PersonValidationService $personValidationService,
        PaginationValidationService $paginationValidationService
    )
    {
        $this->serializer = $serializer;
        $this->achievementsRepository = $achievementRepository;
    }

    /**
     * @Route("/achievements-summary", name="achievements-summary", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Персона"},
     *     summary="Количество достижений и публикаций и последние 3 из них",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="p",
     *          description="Идентификатор пользователя"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Итоговые значения по публикациям и достижениям",
     *          @OA\JsonContent(ref=@Model(type="AchievementSummary::class", groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Некорректные параметры вызова"
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Внутренняя ошибка"
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Ресурс с переданным идентификатором не найден"
     *     )
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\Validation\PersonValidationService $personValidationService
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     * @throws \App\Exception\ResourceNotFoundException
     */
    public function achievementsSummary(
        Request $request,
        PersonValidationService $personValidationService
    ): JsonResponse {
        $personId = $request->query->get('p');
        $personValidationService->validate($personId, 'p');

        $achievementsSummary = new AchievementSummary();

        try {
            $achievementsSummary->setAchievementList($this->achievementsRepository->getAchievements($personId, 0, 3));
            $achievementsSummary->setPublicationsList($this->achievementsRepository->getPublications($personId, 0, 3));
            $achievementsSummary->setAchievementsTotalCount($this->achievementsRepository->getTotalAchievementCount($personId));
            $achievementsSummary->setPublicationsTotalCount($this->achievementsRepository->getTotalPublicationsCount($personId));
        } catch (\Exception $e) {
            throw new DataAccessException('Person');
        }

        return new JsonResponse(
            $this->serializer->serialize($achievementsSummary, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/achievements", name="achievements-list", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Персона"},
     *     summary="Список достижений пользователя",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="p",
     *          description="Идентификатор пользователя"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="of",
     *          description="Номер первого отдаваемого объекта"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="c",
     *          description="Максимальное количество отдаваемых объектов в ответе"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Список объектов достижений пользователя",
     *          @OA\JsonContent(ref=@Model(type="AchievementList::class", groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Некорректные параметры вызова"
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Внутренняя ошибка"
     *     )
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     * @throws \App\Exception\ResourceNotFoundException
     */
    public function achievementsList(
        Request $request,
        PersonValidationService $personValidationService,
        PaginationValidationService $paginationValidationService
    ): JsonResponse {
        $personId = $request->query->get('p');
        $personValidationService->validate($personId, 'p');

        $offset = $request->query->get('of');
        $count = $request->query->get('c');

        $paginationValidationService->valueConstraints = [
            new Assert\GreaterThanOrEqual(0, null, 'Offset value must be above or equals to zero')
        ];
        $paginationValidationService->validate($offset, 'of');

        $paginationValidationService->valueConstraints = [
            new Assert\GreaterThan(0, null, 'Offset value must be above zero')
        ];
        $paginationValidationService->validate($count, 'c');

        /** @var Achievement[] $achievements */
        $achievements = $this->achievementsRepository->getAchievements(
            $personId,
            $offset != null && is_numeric($offset) && $offset >= 0 ? $offset : -1,
            $count !== null && is_numeric($count) && $count > 0 ? $count : -1
        );

        $totalAchievements = $this->achievementsRepository->getTotalAchievementCount($personId);

        $achievementList = new AchievementList();
        $achievementList->setPerson($personId);
        $achievementList->setAchievements($achievements);
        $achievementList->setRemain($totalAchievements - $offset - count($achievements));

        return new JsonResponse(
            $this->serializer->serialize($achievementList, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/publications", name="publications-list", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Персона"},
     *     summary="Список публикаций пользователя",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="p",
     *          description="Идентификатор пользователя"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="of",
     *          description="Номер первого отдаваемого объекта"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="c",
     *          description="Максимальное количество отдаваемых объектов в ответе"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Список объектов публикаций пользователя",
     *          @OA\JsonContent(ref=@Model(type="PublicationList::class", groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Некорректные параметры вызова"
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Внутренняя ошибка"
     *     )
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\Validation\PersonValidationService $personValidationService
     * @param \App\Service\Validation\PaginationValidationService $paginationValidationService
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     * @throws \App\Exception\ResourceNotFoundException
     */
    public function publicationsList(
        Request $request,
        PersonValidationService $personValidationService,
        PaginationValidationService $paginationValidationService
    ): JsonResponse {
        $personId = $request->query->get('p');
        $personValidationService->validate($personId, 'p');

        $offset = $request->query->get('of');
        $count = $request->query->get('c');

        $paginationValidationService->valueConstraints = [
            new Assert\GreaterThanOrEqual(0, null, 'Offset value must be above or equals to zero')
        ];
        $paginationValidationService->validate($offset, 'of');

        $paginationValidationService->valueConstraints = [
            new Assert\GreaterThan(0, null, 'Offset value must be above zero')
        ];
        $paginationValidationService->validate($count, 'c');

        /** @var Publication[] $publications */
        $publications = $this->achievementsRepository->getPublications(
            $personId,
            $offset !== null && is_numeric($offset) && $offset >= 0 ? $offset : -1,
            $count !== null && is_numeric($count) && $count > 0 ? $count : -1
        );

        $totalPublications = $this->achievementsRepository->getTotalPublicationsCount($personId);

        $publicationsList = new PublicationList();
        $publicationsList->setPerson($personId);
        $publicationsList->setPublications($publications);
        $publicationsList->setRemain($totalPublications - $offset - count($publications));

        return new JsonResponse(
            $this->serializer->serialize($publicationsList, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }
}