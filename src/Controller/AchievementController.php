<?php

namespace App\Controller;

use App\Repository\AchievementRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AchievementController
{
    private $achievementsRepository;

    public function __construct(AchievementRepository $achievementRepository)
    {
        $this->achievementsRepository = $achievementRepository;
    }

    /**
     * @Route("/achievements-summary", name="achievements-summary", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function achievementsSummary(Request $request): JsonResponse
    {

    }

    /**
     * @Route("/achievements", name="achievements-list", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function achievementsList(Request $request): JsonResponse
    {

    }

    /**
     * @Route("/publications", name="publications-list", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function publicationsList(Request $request): JsonResponse
    {

    }
}