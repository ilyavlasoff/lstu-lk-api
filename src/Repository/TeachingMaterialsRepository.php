<?php

namespace App\Repository;

use App\Exception\NotFoundException;
use App\Model\DTO\Attachment;
use App\Model\DTO\BinaryFile;
use App\Model\DTO\ExternalLink;
use App\Model\DTO\TeachingMaterial;
use Doctrine\DBAL\Exception;

class TeachingMaterialsRepository extends AbstractRepository
{
    /**
     * @param string $disciplineId
     * @param string $educationId
     * @param string $semesterId
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getDisciplineTeachingMaterials(string $disciplineId, string $educationId, string $semesterId, ?bool $withFiles = false)
    {
        $subQ = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('RC.PLAN')
            ->from('ET_RCONTINGENTS', 'RC')
            ->innerJoin('RC', 'ET_CONTINGENTS', 'EC2', 'EC2.G = RC.G')
            ->where('EC2.OID = :EDUCATION AND RC.CSEMESTER = :SEMESTER')
            ->getSQL();

        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->select('
                ETM.OID AS MATERIAL_ID, 
                ETM.NAME AS MATERIAL_NAME,
                ETMC.NAME AS MATERIAL_TYPE,
                FILE$DOC AS ATT_NAME, 
                ROUND(DBMS_LOB.GETLENGTH(ETM.DOC)/1024) AS DOC_KB,
                ETM.EXTLINK AS FILE_LINK
            ');
        if($withFiles) {
            $queryBuilder->addSelect('ETM.DOC');
        }
        $queryBuilder
            ->from('ET_TEACHINGMATERIALS', 'ETM')
            ->leftJoin('ETM', 'ET_GROUPS', 'EG', 'ETM.G = EG.OID')
            ->leftJoin('EG', 'ET_CONTINGENTS', 'EC', 'EG.OID = EC.G')
            ->leftJoin('ETM', 'ET_MATCATEGORIES', 'ETMC', 'ETM.MATCATEGORY = ETMC.OID')
            ->where('ETM.DISCIPLINE = :DISCIPLINE')
            ->andWhere($queryBuilder->expr()->or('EC.OID = :EDUCATION', 'ETM.G IS NULL'))
            ->andWhere($queryBuilder->expr()->or("ETM.CURRICULUM = ($subQ)", 'ETM.CURRICULUM IS NULL'))
            ->setParameter('DISCIPLINE', $disciplineId)
            ->setParameter('EDUCATION', $educationId)
            ->setParameter('SEMESTER', $semesterId);

        $result = $queryBuilder->execute();

        $teachingMaterials = [];

        while($materialRow = $result->fetchAssociative()) {
            $teachingMaterial = new TeachingMaterial();
            $teachingMaterial->setId($materialRow['MATERIAL_ID']);
            $teachingMaterial->setMaterialName($materialRow['MATERIAL_NAME']);
            $teachingMaterial->setMaterialType($materialRow['MATERIAL_TYPE']);

            if($fileSize = $materialRow['DOC_KB']) {
                $attachment = new Attachment();
                $attachment->setAttachmentSize($fileSize);
                $attachment->setAttachmentName($materialRow['ATT_NAME']);
                $teachingMaterial->setAttachment($attachment);
                if($withFiles && $documentData = $materialRow['DOC']) {
                    $attachment->setB64attachment(base64_encode($documentData));
                }
            }

            if($link = $materialRow['FILE_LINK']) {
                $externalLink = new ExternalLink();
                $externalLink->setLinkContent($link);
                $externalLink->setLinkText($materialRow['ATT_NAME']);
                $teachingMaterial->setExternalLink($externalLink);
            }

            $teachingMaterials[] = $teachingMaterial;
        }

        return $teachingMaterials;
    }

    /**
     * @param string $materialId
     * @return NotFoundException|BinaryFile
     * @throws Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function getTeachingMaterialsAttachment(string $materialId)
    {
        $result = $this->getEntityManager()->getConnection()->createQueryBuilder()
            ->select('ETM.DOC, ETM.FILE$DOC AS DOC_NAME')
            ->from('ET_TEACHINGMATERIALS', 'ETM')
            ->where('ETM.OID = :MATERIAL_ID')
            ->setParameter('MATERIAL_ID', $materialId)
            ->execute()
            ->fetchAllAssociative();

        if(count($result) !== 1) {
            return new NotFoundException('Material');
        }

        $binaryFile = new BinaryFile();
        $binaryFile->setFileContent($result[0]['DOC']);
        $binaryFile->setFilename($result[0]['DOC_NAME']);

        return $binaryFile;
    }
}