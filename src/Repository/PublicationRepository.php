<?php

namespace App\Repository;

use App\Model\DTO\Publication;
use Doctrine\DBAL\Driver\Exception;

class PublicationRepository extends AbstractRepository
{
    /**
     * @param string $person
     * @param int $offset
     * @param int $count
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPublications(string $person, int $offset = -1, int $count = -1): array {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('TP.OID PBL_ID, TP.NAME AS TITLE, TP.DESCRIPTION, TP.DATA PUBLISHED, 
            T.NAME PBL_TYPE, E.NAME PBL_FORM, E.VALUE PBL_FORM_VALUE')
            ->from('ET_PBLWRITERS', 'EP')
            ->innerJoin('EP', 'T_PUBLICATIONS', 'TP', 'EP.PBL = TP.OID')
            ->leftJoin('TP', 'T_PBLTYPE', 'T', 'TP.PBLTYPE = T.OID')
            ->leftJoin('TP', 'ET_PBLFORMS', 'E', 'TP.FORM = E.OID')
            ->where('EP.WRITERLGTU = :PERSON')
            ->orderBy('TP.NAME');

        if($count !== -1 && $offset !== -1) {
            $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($count);
        }

        $result = $queryBuilder
            ->setParameter('PERSON', $person)
            ->execute();

        $publicationList = [];
        while ($publicationRow = $result->fetchAssociative()) {
            $publication = new Publication();
            $publication->setId($publicationRow['PBL_ID']);
            $publication->setTitle($publicationRow['TITLE']);
            $publication->setDescription($publicationRow['DESCRIPTION']);
            $publication->setPublished($publicationRow['PUBLISHED']);
            $publication->setPubType($publicationRow['PBL_TYPE']);
            $publication->setPubForm($publicationRow['PBL_FORM']);
            $publication->setPubFormValue($publicationRow['PBL_FORM_VALUE']);

            $publicationList[] = $publication;
        }

        return $publicationList;
    }

    /**
     * @param string $person
     * @return int
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function getTotalPublicationsCount(string $person): int
    {
        $publications = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('COUNT(EP.OID) AS CNT')
            ->from('ET_PBLWRITERS', 'EP')
            ->where('EP.WRITERLGTU = :PERSON')
            ->setParameter('PERSON', $person)
            ->execute()
            ->fetchAllAssociative();

        return $publications[0]['CNT'];
    }
}