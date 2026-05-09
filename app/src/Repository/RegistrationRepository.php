<?php

namespace App\Repository;

use App\Entity\FactionType;
use App\Entity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Registration>
 */
class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    /**
     * @param list<string> $slugs Valeurs `Registration.event` / `EventType::value`
     */
    public function countDistinctUsersByEventSlugs(array $slugs): int
    {
        if ($slugs === []) {
            return 0;
        }

        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(DISTINCT r.user)')
            ->where('r.event IN (:slugs)')
            ->setParameter('slugs', $slugs)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param list<string> $slugs Valeurs `Registration.event` / `EventType::value`
     *
     * @return array<string, int> Clé = `FactionType::value` ou `__null__` (profil sans faction)
     */
    public function countDistinctUsersByEventSlugsGroupedByUserFaction(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('r')
            ->select('u.faction AS faction', 'COUNT(DISTINCT u.id) AS cnt')
            ->join('r.user', 'u')
            ->where('r.event IN (:slugs)')
            ->setParameter('slugs', $slugs)
            ->groupBy('u.faction')
            ->getQuery()
            ->getArrayResult();

        $out = [];
        foreach ($rows as $row) {
            $faction = $row['faction'];
            if ($faction instanceof FactionType) {
                $key = $faction->value;
            } elseif ($faction === null) {
                $key = '__null__';
            } else {
                $key = (string) $faction;
            }
            $out[$key] = (int) $row['cnt'];
        }

        return $out;
    }

    //    /**
    //     * @return Registration[] Returns an array of Registration objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Registration
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
