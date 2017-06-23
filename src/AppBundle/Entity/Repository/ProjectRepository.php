<?php

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\Project;
use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository
{
    public function getUsedFolders()
    {
        $result = $this->getEntityManager()
                            ->createQueryBuilder()
                            ->select('p.folder')
                            ->from(Project::class, 'p')
                            ->getQuery()
                            ->getScalarResult();

        return array_map(function ($item) {
            return $item['folder'];
        }, $result);

    }
}
