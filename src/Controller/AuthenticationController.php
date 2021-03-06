<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\RestException;
use App\Exception\DataAccessException;
use App\Exception\ValidationException;
use App\Model\DTO\Person;
use App\Model\DTO\AuthenticationData;
use App\Model\ExternalConsumingParam\NotificationReceiver;
use App\Model\QueryParam\RegisterCredentials;
use App\Model\QueryParam\UserIdentifier;
use App\Repository\AuthenticationRepository;
use App\Repository\UserRepository;
use App\Service\NotifierQueryService;
use Doctrine\ODM\MongoDB\MongoDBException;
use Gesdinet\JWTRefreshTokenBundle\Service\RefreshToken;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use mysql_xdevapi\Exception;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Security;

/**
 * Class AuthenticationController
 * @package App\Controller
 * @Route("/api/v1")
 */
class AuthenticationController extends AbstractRestController
{
    public function __construct(SerializerInterface $serializer) {
        parent::__construct($serializer);
    }

    /**
     * @Route("/identify", name="app_user_identify", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Авторизация и регистрация"},
     *     summary="Идентификация студента",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Объект идентификатора",
     *          @OA\JsonContent(ref=@Model(type=UserIdentifier::class, groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Объект идентификации пользователя",
     *          @OA\JsonContent(ref=@Model(type=AuthenticationData::class, groups={"identified"}))
     *     ),
     *     @OA\Response(
     *          response="401",
     *          description="Пользователь с заданными параметрами не найден"
     *     ),
     *     @OA\Response(
     *          response="400",
     *          description="Предоставленные данные невалидны"
     *     )
     * )
     *
     * @param UserIdentifier $userIdentifier
     * @param AuthenticationRepository $authenticationRepository
     * @param JWTTokenManagerInterface $tokenManager
     * @return JsonResponse
     */
    public function identify(
        UserIdentifier $userIdentifier,
        AuthenticationRepository $authenticationRepository,
        JWTTokenManagerInterface $tokenManager
    ): JsonResponse {
        try {
            $oid = $authenticationRepository->identifyUser($userIdentifier);
            $user = $authenticationRepository->persistIdentifiedUser($oid);
        } catch (\Doctrine\DBAL\Exception | MongoDBException $e) {
            throw new DataAccessException($e);
        }

        $jwt = $tokenManager->create($user);
        $authenticationData = new AuthenticationData();
        $authenticationData->setRoles($user->getRoles());
        $authenticationData->setJwtToken($jwt);

        return $this->responseSuccessWithObject($authenticationData);
    }

    /**
     * @Route("/reg", name="app_user_register", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Авторизация и регистрация"},
     *     summary="Регистрация студента",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Объект учетных данных пользователя",
     *          @OA\JsonContent(ref=@Model(type=RegisterCredentials::class, groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="201",
     *          description="Объект идентификации пользователя",
     *          @OA\JsonContent(ref=@Model(type=AuthenticationData::class, groups={"fully-authorized"}))
     *     )
     * )
     *
     * @param RegisterCredentials $credentials
     * @param JWTTokenManagerInterface $tokenManager
     * @param AuthenticationRepository $authenticationRepository
     * @param RefreshTokenManagerInterface $refreshTokenManager
     * @param ParameterBagInterface $parameterBag
     * @param NotifierQueryService $notifierQueryService
     * @return JsonResponse
     * @throws \Exception
     */
    public function register(
        RegisterCredentials $credentials,
        JWTTokenManagerInterface $tokenManager,
        AuthenticationRepository $authenticationRepository,
        RefreshTokenManagerInterface $refreshTokenManager,
        ParameterBagInterface $parameterBag,
        NotifierQueryService $notifierQueryService
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $user = $authenticationRepository->persistRegistration($user, $credentials);
        } catch (MongoDBException $e) {
            throw new DataAccessException($e);
        }

        $jwtToken = $tokenManager->create($user);

        $refreshToken = $refreshTokenManager->create();
        $refreshToken->setUsername($user->getEmail());
        $refreshToken->setRefreshToken();

        $expiresSeconds = $parameterBag->get('expires_seconds');
        $expiresTime = (new \DateTime('now'))->add(new \DateInterval("PT{$expiresSeconds}S"));
        $refreshToken->setValid($expiresTime);
        $refreshTokenManager->save($refreshToken);

        $authenticationData = new AuthenticationData();
        $authenticationData->setRefreshToken($refreshToken->getRefreshToken());
        $authenticationData->setJwtToken($jwtToken);
        $authenticationData->setRoles($user->getRoles());

        $notificationReceiver = new NotificationReceiver();
        $notificationReceiver->setNPersonsOid($user->getDbOid());
        $notificationReceiver->setMutePrivate(false);
        $notificationReceiver->setMuteDiscussion(false);
        $notifierQueryService->addReceiver($notificationReceiver);

        return $this->responseSuccessWithObject($authenticationData);
    }

    /**
     * @Route("/auth", name="app_user_authenticate", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Авторизация и регистрация"},
     *     summary="Авторизация студента",
     *     @OA\RequestBody(
     *          required=true,
     *          description="Объект учетных данных пользователя",
     *          @OA\JsonContent(ref=@Model(type=RegisterCredentials::class, groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Объект идентификации пользователя",
     *          @OA\JsonContent(ref=@Model(type=AuthenticationData::class, groups={"fully-authorized"}))
     *     )
     * )
     */
    public function authenticate(): void {}

    /**
     * @Route("/token/refresh", name="app_jwt_refresh", methods={"POST"})
     * @param Request $request
     * @param RefreshToken $refreshService
     * @return JsonResponse
     */
    public function refreshJwt(Request $request, RefreshToken $refreshService): JsonResponse
    {
        return $refreshService->refresh($request);
    }

    /**
     * @Route("/whoami", name="current_person_get", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Персона"},
     *     summary="Идентификатор текущего пользователя",
     *     @Security (name="Bearer"),
     *     @OA\Response(
     *          response="200",
     *          description="Список объектов публикаций пользователя",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="uoid",
     *                  type="string",
     *                  description="Идентификатор персоны текущеего пользователя",
     *                  example="5:93491220"
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *          response="500",
     *          description="Внутренняя ошибка"
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function currentPersonId(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $person = new Person();
        $person->setUoid($currentUser->getDbOid());

        return $this->responseSuccessWithObject($person);
    }
}