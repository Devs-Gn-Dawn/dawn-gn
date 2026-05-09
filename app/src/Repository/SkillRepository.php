<?php

namespace App\Repository;

use App\Entity\Character;
use App\Entity\ClassType;
use App\Entity\Skill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;

/**
 * @extends ServiceEntityRepository<Skill>
 */
class SkillRepository extends ServiceEntityRepository
{
    private LoggerInterface $logger;

    public function __construct(ManagerRegistry $registry, LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct($registry, Skill::class);
    }

    public function isSkillAvailableForCharacter(Skill $skill, Character $character, ?int $cost = null): bool
    {
        $skillCost = $cost ?? $skill->getBaseCost();
        if ($skillCost > $character->getAvailableSkillsXp()) {
            throw new \Exception('XP insufisant');
        }

        $availableSkills = $this->findAvailableSkillsForCharacter($character);
        if (in_array($skill, $availableSkills) === false) {
            throw new \Exception('Compétence invalide');
        }

        return true;
    }

    public function findAvailableSkillsForCharacter(Character $character, bool $showSkillRestricted = false): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.visibility = true')
            ->andWhere('s.required_classes LIKE :class OR s.required_classes = :emptyArray')
            ->andWhere('s.required_factions LIKE :faction OR s.required_factions = :emptyArray')
            ->setParameter('class', '%' . $character->getClass()->value . '%')
            ->setParameter('faction', '%' . $character->getFaction() . '%')
            ->setParameter('emptyArray', '');

        if (!$showSkillRestricted) {
            $learnedSkillIds = $character->getSkillsLearned()
                ->map(fn($skillLearned) => $skillLearned->getSkill()->getId())
                ->toArray();

            $qb->leftJoin('s.requiredSkills', 'rs')
                ->andWhere('rs.id IS NULL OR rs.id IN (:learnedSkills)')
                ->setParameter('learnedSkills', $learnedSkillIds);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Doit rester aligné avec la logique de filtrage de findAvailableSkillsForCharacter
     * (contraintes required_classes / required_factions : tableau vide = pas de contrainte sur cet axe).
     */
    public function skillMatchesFactionAndClass(Skill $skill, string $factionValue, ClassType $class): bool
    {
        $reqC = $skill->getRequiredClasses();
        $reqF = $skill->getRequiredFactions();
        $classOk = $reqC === [] || \in_array($class->value, $reqC, true);
        $factionOk = $reqF === [] || \in_array($factionValue, $reqF, true);

        return $classOk && $factionOk;
    }

    //    /**
    //     * @return Skill[] Returns an array of Skill objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Skill
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
