<?php

namespace App\Controller;

use App\Model\Mapping\Achievement;
use App\Model\Mapping\Publication;
use App\Model\Response\AchievementList;
use App\Model\Response\AchievementSummary;
use App\Model\Response\PublicationList;
use App\Repository\AchievementRepository;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AchievementController
 * @package App\Controller
 * @Route("/api/v1/person")
 */
class AchievementController extends AbstractRestController
{
    private $achievementsRepository;

    public function __construct(SerializerInterface $serializer, AchievementRepository $achievementRepository)
    {
        parent::__construct($serializer);
        $this->achievementsRepository = $achievementRepository;
    }

    /**
     * @Route("/achievements-summary", name="achievements-summary", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function achievementsSummary(Request $request): JsonResponse
    {
        $personId = $request->query->get('p');
        if(!$personId) {
            throw new \Exception('Incorrect query');
        }

        try {
            $achievementsSummary = new AchievementSummary();
            $achievementsSummary->setAchievementList($this->achievementsRepository->getAchievements($personId, 0, 3));
            $achievementsSummary->setPublicationsList($this->achievementsRepository->getPublications($personId, 0, 3));
            $achievementsSummary->setAchievementsTotalCount($this->achievementsRepository->getTotalAchievementCount($personId));
            $achievementsSummary->setPublicationsTotalCount($this->achievementsRepository->getTotalPublicationsCount($personId));

            return $this->responseSuccessWithObject($achievementsSummary);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @Route("/achievements", name="achievements-list", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function achievementsList(Request $request): JsonResponse
    {
        $personId = $request->query->get('p');
        $offset = $request->query->get('of');
        $count = $request->query->get('c');

        try {
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

            return $this->responseSuccessWithObject($achievementList);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @Route("/publications", name="publications-list", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function publicationsList(Request $request): JsonResponse
    {
        $personId = $request->query->get('p');
        $offset = $request->query->get('of');
        $count = $request->query->get('c');

        try {
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

            return $this->responseSuccessWithObject($publicationsList);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}