<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\DataAccessException;
use App\Exception\SystemException;
use App\Exception\ValidationException;
use App\Model\DTO\Person;
use App\Model\QueryParam\Paginator;
use App\Model\QueryParam\PersonProperties;
use App\Model\QueryParam\PictureSize;
use App\Model\QueryParam\TextQuery;
use App\Model\DTO\ListedResponse;
use App\Model\DTO\ProfilePicture;
use App\Repository\PersonalRepository;
use App\Service\ImageConverter;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class PersonalController
 * @package App\Controller
 * @Route("/api/v1/person")
 */
class PersonalController extends AbstractRestController
{
    private $personalRepository;

    public function __construct(SerializerInterface $serializer, PersonalRepository $personalRepository){
        parent::__construct($serializer);
        $this->personalRepository = $personalRepository;
    }

    /**
     * @Route("", name="person_props_get", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Персона"},
     *     summary="Информация о заданной персоне",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="p",
     *          description="Идентификатор персоны",
     *          example="5:93491220"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Объект персоны",
     *          @OA\JsonContent(ref=@Model(type=Person::class, groups={"Default"}))
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
     * @param \App\Model\QueryParam\Person $person
     * @return JsonResponse
     * @throws DataAccessException
     */
    public function personProperties(\App\Model\QueryParam\Person $person): JsonResponse
    {
        try {
            $personalProps = $this->personalRepository->getPersonalProperties($person->getPersonId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception | \Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($personalProps);
    }

    /**
     * @Route("/props", name="person_properties_edit", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Персона"},
     *     summary="Редактирование информации о пользователе",
     *     @Security(name="Bearer"),
     *     @OA\RequestBody(
     *          description="Объект обновленной информации о пользователе",
     *          @OA\JsonContent(ref=@Model(type=PersonProperties::class, groups={"Default"}))
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Список объектов публикаций пользователя",
     *          @OA\JsonContent(
     *              @OA\Schema(
     *                  @OA\Property (
     *                      property="success",
     *                      description="Флаг успешности операции",
     *                      type="bool",
     *                      example="true"
     *                  )
     *              )
     *          )
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
     * @param PersonProperties $personProperties
     * @return JsonResponse
     */
    public function personPropertiesEdit(PersonProperties $personProperties): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->personalRepository->updatePerson($personProperties, $user->getDbOid());
        } catch (Exception $e) {
            throw new DataAccessException();
        }

        return $this->responseSuccess();
    }

    /**
     * @Route("/pic", name="adapted_userpic_get", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Персона"},
     *     summary="Изображение профиля пользователя ЛК ЛГТУ",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=true,
     *          name="p",
     *          description="Идентификатор персоны",
     *          example="5:93491220"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="size",
     *          description="Константа размера изображения",
     *          example="md"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Объект изображения",
     *          @OA\JsonContent(ref=@Model(type=ProfilePicture::class, groups={"Default"}))
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
     * @param PictureSize $userPicRequest
     * @param \App\Model\QueryParam\Person $person
     * @param ImageConverter $imageConverter
     * @param PersonalRepository $personalRepository
     * @param ParameterBagInterface $parameterBag
     * @return JsonResponse
     *
     */
    public function getProfilePicture(
        PictureSize $userPicRequest,
        \App\Model\QueryParam\Person $person,
        ImageConverter $imageConverter,
        PersonalRepository $personalRepository,
        ParameterBagInterface $parameterBag
    ): JsonResponse
    {
        try {
            $image = $personalRepository->getProfileImage($person->getPersonId());
        } catch (Exception | \Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        try {
            $imagick = new \Imagick();

            if($image) {
                $imagick->readImageBlob($image);
            } else {
                $imagick->readImage($parameterBag->get('images_path') . 'user_default.png');
            }

            $imageConverter->convert($imagick, $userPicRequest->getImageSize(), true);

            $convertedImageBlob = $imagick->getImagesBlob();
        } catch (\ImagickException $e) {
            throw new SystemException($e);
        }

        $profilePicture = new ProfilePicture();
        $profilePicture->setPerson($person->getPersonId());
        $profilePicture->setProfilePicture(base64_encode($convertedImageBlob));

        return $this->responseSuccessWithObject($profilePicture);
    }

    /**
     * @Route("/list", name="persons_list_get", methods={"GET"})
     *
     * @OA\Get(
     *     tags={"Персона"},
     *     summary="Страница списка пользователей с данными о пагинации",
     *     @Security(name="Bearer"),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="of",
     *          description="Номер первого загружаемого элемента"
     *     ),
     *     @OA\Parameter(
     *          in="query",
     *          required=false,
     *          name="c",
     *          description="Максимальное количество отдаваемых элементов на одной странице ответа"
     *     ),
     *     @OA\Response(
     *          response="200",
     *          description="Страница списка личных сообщений пользователя",
     *          @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *              @OA\Property(property="count", type="integer"),
     *              @OA\Property(property="offset", type="integer"),
     *              @OA\Property(property="next_offset", type="integer"),
     *              @OA\Property(property="remains", type="integer"),
     *              @OA\Property(property="payload", type="array", @OA\Items(ref=@Model(type=App\Model\DTO\Person::class, groups={"Default"})))
     *          ))
     *     )
     * )
     *
     * @param TextQuery $query
     * @param Paginator $paginator
     * @param PersonalRepository $personalRepository
     * @return JsonResponse
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getPersonList(
        TextQuery $query,
        Paginator $paginator,
        PersonalRepository $personalRepository
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if($paginator->getOffset() === null) {
            $paginator->setOffset(0);
        }

        if($paginator->getCount() === null) {
            $paginator->setCount(100);
        }

        try {
            $foundedUsers = $personalRepository->getProfileUsers(
                $user->getDbOid(), $query->getQueryString(), $paginator->getOffset(), $paginator->getCount());

            $totalFoundedCount = $personalRepository->getCountOfPersons($query->getQueryString());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        $personList = new ListedResponse();
        $personList->setOffset($paginator->getOffset());
        $personList->setCount(count($foundedUsers));

        $remains = $totalFoundedCount - count($foundedUsers) - $paginator->getOffset();
        $personList->setRemains($remains);

        if($remains) {
            $personList->setNextOffset($paginator->getOffset() + count($foundedUsers));
        }
        $personList->setPayload($foundedUsers);

        return $this->responseSuccessWithObject($personList);
    }

}