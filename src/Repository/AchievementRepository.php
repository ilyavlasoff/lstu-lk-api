<?php

namespace App\Repository;

use App\Model\DTO\Achievement;
use App\Model\DTO\Publication;
use Doctrine\ORM\EntityManagerInterface;

class AchievementRepository extends AbstractRepository
{
    /**
     * @param string $person
     * @return int
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTotalAchievementCount(string $person): int
    {
        $achievements = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('COUNT(EA.OID) AS CNT')
            ->from('ET_ACHIEVEMENTS', 'EA')
            ->innerJoin('EA', 'NPERSONS', 'N', 'EA.PERSON = N.OID')
            ->where('N.OID = :PERSON')
            ->setParameter('PERSON', $person)
            ->execute()
            ->fetchAllAssociative();

        return $achievements[0]['CNT'];
    }

    /**
     * @param string $person
     * @param int $offset
     * @param int $count
     * @param bool $lastFirst
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Exception
     * @throws \Exception
     */
    public function getAchievements(string $person, int $offset = -1, int $count = -1, bool $lastFirst = true): array {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

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
        while ($achievementRow = $result->fetchAssociative()) {
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


}