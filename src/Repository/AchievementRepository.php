<?php

namespace App\Repository;

use App\Model\Mapping\Achievement;
use App\Model\Mapping\Publication;
use App\Model\Response\AchievementSummary;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;

class AchievementRepository
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getTotalAchievementCount(string $person): int
    {
        $achievements = $this->entityManager->getConnection()->createQueryBuilder()
            ->select('COUNT(EA.OID) AS CNT')
            ->from('ET_ACHIEVEMENTS', 'EA')
            ->innerJoin('EA', 'NPERSONS', 'N', 'EA.PERSON = N.OID')
            ->where('N.OID = :PERSON')
            ->setParameter('PERSON', $person)
            ->execute()
            ->fetchAll();
        return $achievements[0]['CNT'];
    }

    public function getTotalPublicationsCount(string $person): int
    {
        $publications = $this->entityManager->getConnection()->createQueryBuilder()
            ->select('COUNT(EP.OID) AS CNT')
            ->from('ET_PBLWRITERS', 'EP')
            ->where('EP.WRITERLGTU = :PERSON')
            ->setParameter('PERSON', $person)
            ->execute()
            ->fetchAll();
        return $publications[0]['CNT'];
    }

    public function getAchievements(string $person, int $offset = -1, int $count = -1, bool $lastFirst = true): array {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

        $queryBuilder
            ->select('EA.OID AS ACH_OID, EA.VALUE AS ACH_NAME, EA.CREATED AS ACH_DATE, EAT.NAME AS ACH_KIND, EP.NAME ACH_TYPE')
            ->from('ET_ACHIEVEMENTS', 'EA')
            ->leftJoin('EA', 'ET_ACHIEVEMENTTYPES', 'EAT', 'EA.ACHIEVEMENTTYPE = EAT.OID')
            ->leftJoin('EA', 'ET_PARTICIPATION', 'EP', 'EA.PARTICIPATION = EP.OID')
            ->innerJoin('EA', 'NPERSONS', 'N', 'EA.PERSON = N.OID')
            ->where('N.OID = :PERSON');

        if($lastFirst) {
            $queryBuilder
                ->orderBy('EA.CREATED', 'DESC');
        } else {
            $queryBuilder
                ->orderBy('EA.CREATED', 'ASC');
        }

        if($count !== -1 && $offset !== -1) {
            $queryBuilder
                ->setFirstResult($offset)
                ->setMaxResults($count);
        }

        $queryBuilder
            ->setParameter('PERSON', $person);
        $result = $queryBuilder->execute();

        $achievementList = [];
        while ($achievementRow = $result->fetch()) {
            $achievement = new Achievement();
            $achievement->setId($achievementRow['ACH_OID']);
            $achievement->setName($achievementRow['ACH_NAME']);
            $achievement->setAchievedDate($achievementRow['ACH_DATE']
                ? new \DateTime($achievementRow['ACH_DATE'])
                : null);
            $achievement->setKind($achievementRow['ACH_KIND']);
            $achievement->setType($achievementRow['ACH_TYPE']);
            $achievementList[] = $achievement;
        }

        return $achievementList;
    }

    public function getPublications(string $person, int $offset = -1, int $count = -1): array {
        $queryBuilder = $this->entityManager->getConnection()->createQueryBuilder();

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
        while ($publicationRow = $result->fetch()) {
            $publication = new Publication();
            $publication->setId($publicationRow['PBL_ID']);
            $publication->setTitle($publicationRow['TITLE']);
            $publication->setDescription($publicationRow['DESCRIPTION']);
            $publication->setPublished($publicationRow['PUBLISHED']);
            $publication->setTitle($publicationRow['PBL_TYPE']);
            $publication->setPubForm($publicationRow['PBL_FORM']);
            $publication->setPubFormValue($publicationRow['PBL_FORM_VALUE']);

            $publicationList[] = $publication;
        }

        return $publicationList;
    }
}