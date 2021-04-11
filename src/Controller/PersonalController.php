<?php

namespace App\Controller;

use App\Document\User;
use App\Exception\ValueNotFoundException;
use App\Model\Mapping\Person;
use App\Repository\PersonalRepository;
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

    public function __construct(SerializerInterface $serializer, PersonalRepository $personalRepository)
    {
        parent::__construct($serializer);
        $this->personalRepository = $personalRepository;
    }

    /**
     * @Route("/props", name="get_person_props", methods={"GET"})
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function personProperties(Request $request): JsonResponse
    {
        $userId = $request->query->get('usr');
        if(!$userId) {
            throw new \Exception('Incorrect query');
        }

        try {
            $personalProps = $this->personalRepository->getPersonalProperties($userId);

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
     * @Route("/props", name="edit_person_properties", methods={"POST"})
     *
     * @param Request $request
     */
    public function personPropertiesEdit(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        if(!$currentUser) {
            throw new \Exception('Unauthorized');
        }

        /** @var Person $editedPerson */
        $editedPerson = $serializer->deserialize($request->getContent(), Person::class, 'json');

        try {
            $personalProps = $this->personalRepository->getPersonalProperties($currentUser->getDbOid());
        } catch (\Exception $e) {
            throw $e;
        }

        $mergedPerson = $personalProps->mergeChanges($editedPerson);
        if(count($validationErrors = $validator->validate($mergedPerson))) {
            throw new \Exception('Incorrect validation');
        }

        try {
            $this->personalRepository->updatePerson($mergedPerson);
        } catch (\Exception $e) {
            throw $e;
        }
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
            throw new \Exception('User not found');
        }

        $person = new Person();
        $person->setUoid($currentUser->getDbOid());

        return $this->responseSuccessWithObject($person);
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
            throw new \Exception('Incorect argument');
        }


    }

}