<?php

namespace App\Repository;

use App\Entity\Character;
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

    public function isSkillAvailableForCharacter(Skill $skill, Character $character): bool
    {
        $requiredClasses = $skill->getRequiredClasses();
        $requiredFactions = $skill->getRequiredFactions();

        if ($skill->getBaseCost() > $character->getAvailableSkillsXp()) {
            throw new \Exception('XP insufisant');
        }

        if (!empty($requiredClasses) && !in_array($character->getClass(), $requiredClasses)) {
            throw new \Exception('Classe invalide ' . $character->getClass() . ' ' . json_encode($requiredClasses));
        }

        if (!empty($requiredFactions) && in_array($character->getFaction(), $requiredFactions) === false) {
            throw new \Exception('Faction invalide ' . $character->getFaction() . ' ' . json_encode($requiredFactions));
        }

        // check required skills
        $requiredSkills = $skill->getRequiredSkills();
        foreach ($requiredSkills as $requiredSkill) {
            $skillLearned = $character->getSkillsLearned()->filter(
                fn($skillLearned) => $skillLearned->getSkill()->getId() === $requiredSkill->getId()
            )->first();
            if ($skillLearned === null) {
                throw new \Exception('Compétence requise manquante');
            }
        }

        return true;
    }

    public function findAvailableSkillsForCharacter(Character $character, bool $showSkillRestricted = false): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.visibility = true')
            ->andWhere('s.required_classes LIKE :class OR s.required_classes = :emptyArray')
            ->andWhere('s.required_factions LIKE :faction OR s.required_factions = :emptyArray')
            ->setParameter('class', '%' . $character->getClass() . '%')
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
