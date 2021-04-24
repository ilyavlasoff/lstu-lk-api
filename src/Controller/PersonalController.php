<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\DataAccessException;
use App\Exception\InvalidUserException;
use App\Exception\ValidationException;
use App\Exception\ResourceNotFoundException;
use App\Model\Mapping\Person;
use App\Repository\PersonalRepository;
use App\Service\Validation\PersonValidationService;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
class PersonalController extends AbstractController
{
    private $serializer;
    private $personalRepository;

    public function __construct(SerializerInterface $serializer, PersonalRepository $personalRepository){
        $this->serializer = $serializer;
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\Validation\PersonValidationService $personValidationService
     * @return JsonResponse
     * @throws \App\Exception\DataAccessException
     * @throws \App\Exception\ParameterExistenceException
     * @throws \App\Exception\ResourceNotFoundException
     */
    public function personProperties(
        Request $request,
        PersonValidationService $personValidationService
    ): JsonResponse {
        $personId = $request->query->get('p');
        $personValidationService->validate($personId, 'p');

        try {
            $personalProps = $this->personalRepository->getPersonalProperties($personId);
        } catch (\Exception $e) {
            throw new DataAccessException('Person', $e);
        }

        return new JsonResponse(
            $this->serializer->serialize($personalProps, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/props", name="edit_person_properties", methods={"POST"})
     *
     * @throws \App\Exception\ValidationException
     * @throws \App\Exception\DataAccessException
     * @throws \App\Exception\InvalidUserException
     */
    public function personPropertiesEdit(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if(!$currentUser || !$currentUser instanceof User) {
            throw new InvalidUserException();
        }

        /** @var Person $editedPerson */
        $editedPerson = $serializer->deserialize($request->getContent(), Person::class, 'json');

        try {
            $personalProps = $this->personalRepository->getPersonalProperties($currentUser->getDbOid());
        } catch (\Exception $e) {
            throw new DataAccessException('Person');
        }

        $mergedPerson = $personalProps->mergeChanges($editedPerson);
        if(count($validationErrors = $validator->validate($mergedPerson))) {
            throw new ValidationException($validationErrors, 'Person');
        }

        $this->personalRepository->updatePerson($mergedPerson);
        return new JsonResponse(json_encode(['success' => true]), Response::HTTP_OK ,[], true);
    }

    /**
     * @Route("/whoami", name="get_current_person", methods={"GET"})
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function currentPersonId(): JsonResponse
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if(!$currentUser) {
            throw new InvalidUserException();
        }

        $person = new Person();
        $person->setUoid($currentUser->getDbOid());

        return new JsonResponse(
            $this->serializer->serialize($person, 'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/userpic/{size}", name="get_adapted_userpic")
     * @return JsonResponse
     */
    public function userpic(string $size, Request $request): JsonResponse
    {
        if(!in_array($size, ['sm', 'md', 'lg'])) {
            throw new \Exception('Incorrect argument');
        }

        $personId = $request->query->get('usr');
        if(!$personId) {
            throw new InvalidUserException();
        }


    }

}