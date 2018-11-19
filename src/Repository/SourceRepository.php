<?php

namespace App\Repository;

use App\Entity\Source;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Source|null find($id, $lockMode = null, $lockVersion = null)
 * @method Source|null findOneBy(array $criteria, array $orderBy = null)
 * @method Source[]    findAll()
 * @method Source[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SourceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Source::class);
    }

//    /**
//     * @return Source[] Returns an array of Source objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Source
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findWithText($text = "")
    {

        $sql = 'SELECT s FROM Source::class s WHERE s.name = :text ';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('text', $text);

        $result = $query->getResult();
        return $result != null ? $result[0] : null;

    }

    public function findAllByAdmin($user)
    {

        $sql =
            'SELECT s FROM Source::class s 
            INNER JOIN s.subjects sub 
            LEFT JOIN sub.authorisedAdmins admins 
            WHERE admins = :user ';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('user', $user);

        return $query->getResult() ?? [];

    }

    public function findAllWithDate($lastCall)
    {

        $sql = 'SELECT s FROM Source::class s WHERE s.updated > :last ';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('last', $lastCall);

        return $query->getResult() ?? [];

    }
}
