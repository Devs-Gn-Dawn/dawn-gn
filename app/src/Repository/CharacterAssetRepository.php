<?php

namespace App\Repository;

use App\Entity\CharacterAsset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CharacterAsset>
 *
 * @method CharacterAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method CharacterAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method CharacterAsset[]    findAll()
 * @method CharacterAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CharacterAssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CharacterAsset::class);
    }

    public function save(CharacterAsset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CharacterAsset $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
