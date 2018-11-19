<?php

namespace App\Repository;

use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Subject|null find($id, $lockMode = null, $lockVersion = null)
 * @method Subject|null findOneBy(array $criteria, array $orderBy = null)
 * @method Subject[]    findAll()
 * @method Subject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubjectRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Subject::class);
    }

    public function findWithText($text = "")
    {

        $sql = 'SELECT s FROM App\Entity\Subject s 
            INNER JOIN s.subject sub 
            INNER JOIN sub.topic t 
            WHERE s.name = :text 
            ORDER BY s.name, sub.name, t.name';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('text', $text);

        $result = $query->getResult();
        return $result != null ? $result[0] : null;

    }

    public function findSubject($sourceId = 0, $user = null)
    {

        $sql = 'SELECT s FROM App\Entity\Subject s 
            INNER JOIN s.source src 
            INNER JOIN s.topics t 
            LEFT JOIN s.authorisedUsers usr 
            WHERE src.id = :sourceId AND (s.public = true OR (s.public = false AND usr = :user)) ';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('sourceId', $sourceId);
        $query->setParameter('user', $user);

        $result = $query->getResult();
        return $result != null ? $result : [];

    }

    public function findSubjectForAdmin($user = null)
    {

        $sql = 'SELECT s FROM App\Entity\Subject s INNER JOIN s.authorisedAdmins admins WHERE admins = :user ';


        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('user', $user);


        $result = $query->getResult();
        return $result != null ? $result : [];

    }

    public function findSubjectFromSourceForAdmin($sourceId = 0, $user = null)
    {

        $sql = 'SELECT s FROM App\Entity\Subject s 
            INNER JOIN s.source src 
            INNER JOIN s.topics t 
            LEFT JOIN s.authorisedAdmins admins 
            WHERE src.id = :sourceId 
            AND admins = :user ';


        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('sourceId', $sourceId);
        $query->setParameter('user', $user);


        $result = $query->getResult();
        return $result != null ? $result : [];

    }
}
