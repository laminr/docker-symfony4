<?php

namespace App\Repository;

use App\Entity\Focus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Focus|null find($id, $lockMode = null, $lockVersion = null)
 * @method Focus|null findOneBy(array $criteria, array $orderBy = null)
 * @method Focus[]    findAll()
 * @method Focus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FocusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Focus::class);
    }

//    /**
//     * @return Focus[] Returns an array of Focus objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Focus
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
