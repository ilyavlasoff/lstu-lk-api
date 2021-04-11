<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\InvalidRequestException;
use App\Exception\ValueNotFoundException;
use App\Repository\PersonalRepository;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Model\Mapping\PersonalProperties;

/**
 * Class PersonalController
 * @package App\Controller
 * @Route("/api/v1/person")
 */
class PersonalController extends AbstractRestController
{
    private $personalRepository;

    public function __construct(SerializerInterface $serializer, PersonalRepository $personalRepository)
    {
        parent::__construct($serializer);
        $this->personalRepository = $personalRepository;
    }

    /**
     * @Route("/props/{id}", name="get_person_props", methods={"GET"})
     * @param string $id
     * @return JsonResponse
     *
     * @OA\Get(
     *     tags={"Пользователь"},
     *     summary="Получение объекта свойств пользователя",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="path",
     *          name="id",
     *          description="Идентификатор пользователя",
     *          required=true
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Объект пользователя",
     *          @OA\JsonContent(ref=@Model(type=PersonalProperties::class, groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Студент с данными идентификатором не найден"
     *     )
     * )
     * @throws \Exception
     */
    public function getPersonProperties(string $id): JsonResponse
    {
        try {
            $personalProps = $this->personalRepository->getPersonalProperties($id);

            if (!$personalProps) {
                throw new ValueNotFoundException('Person', 'Person not found');
            }

            return $this->responseSuccessWithObject($personalProps);

        } catch (\Exception $e)
        {
            throw $e;
        }
    }

    /**
     * @Route("/whoami", name="get_current_person", methods={"GET"})
     * @return JsonResponse
     *
     * @OA\Get(
     *     tags={"Пользователь"},
     *     summary="Получение текущего пользователя",
     *     @Security(name="Bearer"),
     *     @OA\Response(
     *          response="200",
     *          description="Объект пользователя",
     *          @OA\JsonContent(ref=@Model(type=PersonalProperties::class, groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="404",
     *          description="Зарегистрированный студент не найден"
     *     )
     * )
     * @throws \Exception
     */
    public function getCurrentPerson(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if(!$currentUser) {
            throw new \Exception('User not found');
        }

        try {
            $personalProps = $this->personalRepository->getPersonalProperties($currentUser->getDbOid());

            if (!$personalProps) {
                throw new ValueNotFoundException('Person', 'Person not found');
            }

            return $this->responseSuccessWithObject($personalProps);

        } catch (\Exception $e)
        {
            throw $e;
        }
    }

}