<?php

namespace App\Controller;

use App\Exception\DataAccessException;
use App\Model\DTO\ListedResponse;
use App\Model\DTO\Publication;
use App\Model\QueryParam\Paginator;
use App\Model\QueryParam\Person;
use App\Repository\PublicationRepository;
use Doctrine\DBAL\Exception;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * Class PublicationController
 * @package App\Controller
 * @Route("/api/v1/person/publications")
 */
class PublicationController extends AbstractRestController
{
    private $publicationRepository;

    public function __construct(SerializerInterface $serializer, PublicationRepository $publicationRepository)
    {
        parent::__construct($serializer);
        $this->publicationRepository = $publicationRepository;
    }

    /**
     * @Route("/list", name="publications_list_get", methods={"GET"})
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
     * @param Person $person
     * @param Paginator $paginator
     * @return JsonResponse
     */
    public function publicationsList(Person $person, Paginator $paginator): JsonResponse
    {
        $offset = $paginator->getOffset();
        $count = $paginator->getCount();

        try {
            /** @var Publication[] $publications */
            $publications = $this->publicationRepository->getPublications(
                $person->getPersonId(),
                $offset !== null && is_numeric($offset) && $offset >= 0 ? $offset : -1,
                $count !== null && is_numeric($count) && $count > 0 ? $count : -1
            );

            $totalPublications = $this->publicationRepository->getTotalPublicationsCount($person->getPersonId());
        } catch (Exception $e) {
            throw new DataAccessException($e);
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
            throw new DataAccessException($e);
        }

        $publicationsList = new ListedResponse();
        $publicationsList->setPayload($publications);

        $pblCount = count($publications);
        $publicationsList->setOffset($offset);
        $publicationsList->setCount($pblCount);

        $remains = $totalPublications - $offset - $pblCount;
        $publicationsList->setRemains($remains);
        if($remains > 0) {
            $publicationsList->setNextOffset($offset + $pblCount);
        }

        return $this->responseSuccessWithObject($publicationsList);
    }
}