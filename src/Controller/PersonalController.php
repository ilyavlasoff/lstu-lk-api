<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\DataAccessException;
use App\Exception\SystemException;
use App\Exception\ValidationException;
use App\Model\Mapping\Person;
use App\Model\Request\Paginator;
use App\Model\Request\PersonProperties;
use App\Model\Request\PictureSize;
use App\Model\Request\TextQuery;
use App\Model\Response\ListedResponse;
use App\Model\Response\ProfilePicture;
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
     * @Route("/info", name="get_person_props", methods={"GET"})
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
     *          @OA\JsonContent(ref=@Model(type="App\Model\Mapping\Person::class", groups={"Default"}))
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
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     */
    public function personProperties(\App\Model\Request\Person $person): JsonResponse
    {
        try {
            $personalProps = $this->personalRepository->getPersonalProperties($person->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        }

        return $this->responseSuccessWithObject($personalProps);
    }

    /**
     * @Route("/info", name="edit_person_properties", methods={"POST"})
     *
     * @OA\Post(
     *     tags={"Персона"},
     *     summary="Редактирование информации о пользователе",
     *     @Security(name="Bearer"),
     *     @OA\RequestBody(
     *          description="Объект обновленной информации о пользователе",
     *          @OA\JsonContent(ref=@Model(type="App\Model\Request\PersonProperties::class", groups={"Default"}))
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
     * @param \App\Model\Request\PersonProperties $personProperties
     * @return \Symfony\Component\HttpFoundation\JsonResponse
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
     * @Route("/pic", name="get_adapted_userpic", methods={"GET"})
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
     *          @OA\JsonContent(ref=@Model(type="App\Model\Response\ProfilePicture::class", groups={"Default"}))
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
     * @param \App\Model\Request\PictureSize $userPicRequest
     * @param \App\Model\Request\Person $person
     * @param \App\Service\ImageConverter $imageConverter
     * @param \App\Repository\PersonalRepository $personalRepository
     * @param \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface $parameterBag
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getProfilePicture(
        PictureSize $userPicRequest,
        \App\Model\Request\Person $person,
        ImageConverter $imageConverter,
        PersonalRepository $personalRepository,
        ParameterBagInterface $parameterBag
    ): JsonResponse
    {
        $image = $personalRepository->getProfileImage($person->getPersonId());

        $imagick = new \Imagick();

        try {
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
     * @Route("/list", name="get_persons_list", methods={"GET"})
     */
    public function getPersonList(TextQuery $query, Paginator $paginator, PersonalRepository $personalRepository): JsonResponse
    {
        /** @var \App\Document\User $user */
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