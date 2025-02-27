<?php

namespace App\Repository;

use App\Entity\EmergencyContact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmergencyContact>
 *
 * @method EmergencyContact|null find($id, $lockMode = null, $lockVersion = null)
 * @method EmergencyContact|null findOneBy(array $criteria, array $orderBy = null)
 * @method EmergencyContact[]    findAll()
 * @method EmergencyContact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmergencyContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmergencyContact::class);
    }
}
