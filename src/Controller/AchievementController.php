<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\DTO\Achievement;
use App\Model\DTO\Publication;
use App\Model\QueryParam\Paginator;
use App\Model\QueryParam\Person;
use App\Model\DTO\AchievementSummary;
use App\Model\DTO\ListedResponse;
use App\Repository\AchievementRepository;
use App\Repository\PublicationRepository;
use Doctrine\DBAL\Exception;
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
 * @Route("/api/v1/person/achievements")
 */
class AchievementController extends AbstractRestController
{
    private $achievementsRepository;

    public function __construct(
        SerializerInterface $serializer,
        AchievementRepository $achievementRepository
    )
    {
        parent::__construct($serializer);
        $this->achievementsRepository = $achievementRepository;
    }

    /**
     * @Route("/summary", name="achievements_summary_get", methods={"GET"})
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
     *          @OA\JsonContent(ref=@Model(type="App\Model\DTO\AchievementSummary::class", groups={"Default"}))
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
     * @param Person $person
     * @param PublicationRepository $publicationRepository
     * @return JsonResponse
     * @throws \Exception
     */
    public function achievementsSummary(Person $person, PublicationRepository $publicationRepository): JsonResponse {

        try {
            $achievementList = $this->achievementsRepository->getAchievements($person->getPersonId(), 0, 3);
            $publicationsList = $publicationRepository->getPublications($person->getPersonId(), 0, 3);
            $achievementCount = $this->achievementsRepository->getTotalAchievementCount($person->getPersonId());
            $publicationsCount = $publicationRepository->getTotalPublicationsCount($person->getPersonId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $achievementsSummary = new AchievementSummary();
        $achievementsSummary->setAchievementList($achievementList);
        $achievementsSummary->setPublicationsList($publicationsList);
        $achievementsSummary->setAchievementsTotalCount($achievementCount);
        $achievementsSummary->setPublicationsTotalCount($publicationsCount);

        return $this->responseSuccessWithObject($achievementsSummary);
    }

    /**
     * @Route("/list", name="achievements-list", methods={"GET"})
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
     *          @OA\JsonContent(ref=@Model(type="App\Model\DTO\ListedResponse::class", groups={"Default"}))
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
     * @param Person $person
     * @param Paginator $paginator
     * @return JsonResponse
     * @throws \Exception
     */
    public function achievementsList(
        Person $person,
        Paginator $paginator
    ): JsonResponse {
        $offset = $paginator->getOffset();
        $count = $paginator->getCount();

        try {
            /** @var Achievement[] $achievements */
            $achievements = $this->achievementsRepository->getAchievements(
                $person->getPersonId(),
                $offset != null && is_numeric($offset) && $offset >= 0 ? $offset : -1,
                $count !== null && is_numeric($count) && $count > 0 ? $count : -1
            );

            $totalAchievements = $this->achievementsRepository->getTotalAchievementCount($person->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $achievementList = new ListedResponse();
        $achievementList->setCount(count($achievements));
        $achievementList->setOffset($paginator->getOffset());
        $achievementList->setPayload($achievements);

        $remains = $totalAchievements - $offset - count($achievements);
        $achievementList->setRemains($remains);

        if($remains) {
            $achievementList->setNextOffset($paginator->getCount());
        }

        return $this->responseSuccessWithObject($achievementList);
    }

}