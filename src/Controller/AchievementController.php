<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\Mapping\Achievement;
use App\Model\Mapping\Publication;
use App\Model\Request\Paginator;
use App\Model\Request\Person;
use App\Model\Response\AchievementList;
use App\Model\Response\AchievementSummary;
use App\Model\Response\PublicationList;
use App\Repository\AchievementRepository;
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
 * @Route("/api/v1/person")
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
     * @param Person $person
     * @return JsonResponse
     * @throws DataAccessException|\Exception
     */
    public function achievementsSummary(Person $person): JsonResponse {

        try {
            $achievementList = $this->achievementsRepository->getAchievements($person->getPersonId(), 0, 3);
            $publicationsList = $this->achievementsRepository->getPublications($person->getPersonId(), 0, 3);
            $achievementCount = $this->achievementsRepository->getTotalAchievementCount($person->getPersonId());
            $publicationsCount = $this->achievementsRepository->getTotalPublicationsCount($person->getPersonId());
        } catch (Exception $e) {
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
     * @param \App\Model\Request\Person $person
     * @param \App\Model\Request\Paginator $paginator
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
        }

        $achievementList = new AchievementList();
        $achievementList->setPerson($person->getPersonId());
        $achievementList->setAchievements($achievements);
        $achievementList->setRemain($totalAchievements - $offset - count($achievements));

        return $this->responseSuccessWithObject($achievementList);
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
     * @param \App\Model\Request\Person $person
     * @param \App\Model\Request\Paginator $paginator
     * @return JsonResponse
     */
    public function publicationsList(Person $person, Paginator $paginator): JsonResponse
    {
        $offset = $paginator->getOffset();
        $count = $paginator->getCount();

        try {
            /** @var Publication[] $publications */
            $publications = $this->achievementsRepository->getPublications(
                $person->getPersonId(),
                $offset !== null && is_numeric($offset) && $offset >= 0 ? $offset : -1,
                $count !== null && is_numeric($count) && $count > 0 ? $count : -1
            );

            $totalPublications = $this->achievementsRepository->getTotalPublicationsCount($person->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $publicationsList = new PublicationList();
        $publicationsList->setPerson($person->getPersonId());
        $publicationsList->setPublications($publications);
        $publicationsList->setRemain($totalPublications - $offset - count($publications));

        return $this->responseSuccessWithObject($publicationsList);
    }
}