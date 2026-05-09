<?php

namespace App\Repository;

use App\Entity\FactionType;
use App\Entity\Registration;
use App\Entity\User;
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
     * @return array<string, int> Clé = `FactionType::value`, ou `__null__` si `User::getResolvedFaction` est vide
     */
    public function countDistinctUsersByEventSlugsGroupedByUserFaction(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $idRows = $this->createQueryBuilder('r')
            ->select('DISTINCT u.id AS uid')
            ->join('r.user', 'u')
            ->where('r.event IN (:slugs)')
            ->setParameter('slugs', $slugs)
            ->getQuery()
            ->getScalarResult();

        $ids = array_values(array_unique(array_map(static fn (array $row): int => (int) $row['uid'], $idRows)));
        if ($ids === []) {
            return [];
        }

        /** @var User[] $users */
        $users = $this->getEntityManager()->getRepository(User::class)->findBy(['id' => $ids]);

        $known = array_map(static fn (FactionType $f) => $f->value, FactionType::cases());
        $out = [];
        foreach ($users as $user) {
            $resolved = $user->getResolvedFaction();
            if ($resolved instanceof FactionType) {
                $key = $resolved->value;
            } else {
                $key = '__null__';
            }
            if ($key !== '__null__' && !\in_array($key, $known, true)) {
                $key = '__null__';
            }
            $out[$key] = ($out[$key] ?? 0) + 1;
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
