<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\DataAccessException;
use App\Exception\SystemException;
use App\Exception\ValidationException;
use App\Model\Mapping\Person;
use App\Model\Request\PersonProperties;
use App\Model\Request\UserPic;
use App\Model\Response\ProfilePicture;
use App\Repository\PersonalRepository;
use App\Service\ImageConverter;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
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
     * @Route("/props", name="get_person_props", methods={"GET"})
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
     *          @OA\JsonContent(ref=@Model(type="App\Model\Response\PublicationList::class", groups={"Default"}))
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
     * @Route("/props", name="edit_person_properties", methods={"POST"})
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
     * @Route("/whoami", name="get_current_person", methods={"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function currentPersonId(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $person = new Person();
        $person->setUoid($currentUser->getDbOid());

        return $this->responseSuccessWithObject($person);
    }

    /**
     * @Route("/userpic", name="get_adapted_userpic")
     *
     * @param \App\Model\Request\UserPic $userPicRequest
     * @param \App\Service\ImageConverter $imageConverter
     * @param \App\Repository\PersonalRepository $personalRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \App\Exception\SystemException
     */
    public function getProfilePicture(UserPic $userPicRequest, ImageConverter $imageConverter, PersonalRepository $personalRepository): JsonResponse
    {
        $image = $personalRepository->getProfileImage($userPicRequest->getPersonId());

        $imagick = new \Imagick();

        try {
            if($image) {
                $imagick->readImageBlob($image);
            } else {
                $imagick->readImage('default_userpic.png');
            }

            $imageConverter->convert($imagick, $userPicRequest->getImageSize());

            $convertedImageBlob = $imagick->getImagesBlob();
        } catch (\ImagickException $e) {
            throw new SystemException($e);
        }

        $profilePicture = new ProfilePicture();
        $profilePicture->setPerson($userPicRequest->getPersonId());
        $profilePicture->setProfilePicture(base64_encode($convertedImageBlob));

        return $this->responseSuccessWithObject($profilePicture);
    }

}