<?php

namespace App\Repository;

use App\Entity\Character;
use App\Entity\CharacterType;
use App\Entity\FactionType;
use App\Entity\Registration;
use App\Entity\ValidationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Character>
 */
class CharacterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Character::class);
    }

    /**
     * Fiches principal + validées dont le joueur a au moins une inscription sur l’un des opus donnés.
     *
     * @param list<string> $slugs Valeurs `Registration.event` / `EventType::value`
     */
    public function countValidatedMainForUsersRegisteredToEvents(array $slugs): int
    {
        if ($slugs === []) {
            return 0;
        }

        return (int) $this->createQueryBuilder('c')
            ->select('COUNT(DISTINCT c.id)')
            ->innerJoin('c.user', 'u')
            ->innerJoin(
                Registration::class,
                'r',
                'WITH',
                'r.user = u AND r.event IN (:slugs)'
            )
            ->andWhere('c.validationType = :valide')
            ->andWhere('c.type = :main')
            ->setParameter('slugs', $slugs)
            ->setParameter('valide', ValidationType::VALIDE)
            ->setParameter('main', CharacterType::MAIN)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param list<string> $slugs Valeurs `Registration.event` / `EventType::value`
     *
     * @return array<string, int> Clé = `FactionType::value` ou `__other__` (fiche sans faction ou valeur inconnue)
     */
    public function countValidatedMainForUsersRegisteredToEventsGroupedByCharacterFaction(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $known = array_map(static fn (FactionType $f) => $f->value, FactionType::cases());

        $rows = $this->createQueryBuilder('c')
            ->select('c.faction AS factionStr', 'COUNT(DISTINCT c.id) AS cnt')
            ->innerJoin('c.user', 'u')
            ->innerJoin(
                Registration::class,
                'r',
                'WITH',
                'r.user = u AND r.event IN (:slugs)'
            )
            ->andWhere('c.validationType = :valide')
            ->andWhere('c.type = :main')
            ->setParameter('slugs', $slugs)
            ->setParameter('valide', ValidationType::VALIDE)
            ->setParameter('main', CharacterType::MAIN)
            ->groupBy('c.faction')
            ->getQuery()
            ->getArrayResult();

        $out = [];
        foreach ($rows as $row) {
            $str = $row['factionStr'];
            $key = ($str === null || $str === '' || !\in_array($str, $known, true)) ? '__other__' : $str;
            $out[$key] = ($out[$key] ?? 0) + (int) $row['cnt'];
        }

        return $out;
    }

    //    /**
    //     * @return Character[] Returns an array of Character objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Character
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
