<?php

namespace App\Repository;

use App\Entity\Question;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Question::class);
    }


    // http://stackoverflow.com/questions/23825237/doctrine-how-to-use-replace-function
    /**
     * @param string $text
     * @return Question[]
     */
    public function findWithText($text = "")
    {
//        $text = preg_replace('/\s+/', '', $text);

        $sql = "SELECT q FROM App\Entity\Question q 
            WHERE replace(q.canonical,' ','') LIKE replace(:text,' ','')";

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('text', "$text");

        $result = $query->getResult();
        return $result != null ? $result[0] : null;

    }

    /**
     * @param User $user
     * @param int $id
     * @return Question|null
     */
    public function findOneByIdWithUser($user, $id = 0)
    {

        $sql = 'SELECT q FROM App\Entity\Question q 
            LEFT JOIN q.follow fw WITH fw.user = :user
            LEFT JOIN q.focus fs WITH fs.user = :user
            WHERE q.id = :id';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('id', $id);
        $query->setParameter('user', $user);

        $result = $query->getResult();
        return $result != null ? $result[0] : null;

    }

    /**
     * @param int $sourceId
     * @param ?string $tag
     * @return Question[]
     */
    public function findForSource($sourceId = 0, $tag = null)
    {

        $sql = 'SELECT q FROM App\Entity\Question q 
            INNER JOIN q.topic t 
            LEFT JOIN q.tags tags 
            INNER JOIN t.subject s 
            INNER JOIN s.source src 
            WHERE src.id = :id ';

        if ($tag != null) {
            $sql .= 'AND tags.value = :tag ';
        }

        $sql .= 'ORDER BY s.name ASC';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('id', $sourceId);

        if ($tag != null) {
            $query->setParameter('tag', $tag);
        }

        return $query->getResult() ?? [];

    }

    /**
     * @param int $sourceId
     * @param ?User $user
     * @return Question[]
     */
    public function findForSourceByAdmin($sourceId = 0, $user = null)
    {

        $sql = 'SELECT q FROM App\Entity\Question q 
            INNER JOIN q.topic t 
            INNER JOIN t.subject s 
            INNER JOIN s.authorisedAdmins admins 
            INNER JOIN s.source src 
            WHERE src.id = :id 
            AND admins = :user 
            ORDER BY s.name ASC';

        $query = $this->getEntityManager()->createQuery($sql);

        $query->setParameter('id', $sourceId);
        $query->setParameter('user', $user);

        return $query->getResult() ?? [];

    }

    public function findForSubject($subjectId = 0, $tag = null)
    {

        $sql = 'SELECT q FROM App\Entity\Question q 
            INNER JOIN q.topic t 
            LEFT JOIN q.tags tags 
            INNER JOIN t.subject s
            WHERE s.id = :id ';

        if ($tag != null) {
            $sql .= 'AND tags.value = :tag ';
        }

        $sql .= 'ORDER BY t.name ASC';

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('id', $subjectId);

        if ($tag != null) {
            $query->setParameter('tag', $tag);
        }

        return $query->getResult() ?? [];

    }

    public function findForSubjectByAdmin($subjectId = 0, $user = null)
    {

        $sql = 'SELECT q FROM App\Entity\Question q 
            INNER JOIN q.topic t 
            INNER JOIN t.subject s 
            INNER JOIN s.authorisedAdmins admins 
            WHERE s.id = :id 
            AND admins = :user 
            ORDER BY t.name ASC';

        $query = $this->getEntityManager()->createQuery($sql);

        $query->setParameter('id', $subjectId);
        $query->setParameter('user', $user);

        return $query->getResult() ?? [];

    }

    public function findForTopic($topicId = 0, $tag = null)
    {

        $sql = 'SELECT q FROM App\Entity\Question q 
            INNER JOIN q.topic t 
            LEFT JOIN q.tags tags 
            WHERE t.id = :id ';

        if ($tag != null) {
            $sql .= 'AND tags.value = :tag';
        }

        $query = $this->getEntityManager()->createQuery($sql);
        $query->setParameter('id', $topicId);

        if ($tag != null) {
            $query->setParameter('tag', $tag);
        }

        return $query->getResult() ?? [];

    }

    public function findForTopicByAdmin($topicId = 0, $user = null)
    {

        $sql = 'SELECT q FROM App\Entity\Question q 
            INNER JOIN q.topic t 
            INNER JOIN t.subject s 
            INNER JOIN s.authorisedAdmins admins 
            WHERE t.id = :id 
            AND admins = :user ';

        $query = $this->getEntityManager()->createQuery($sql);

        $query->setParameter('id', $topicId);
        $query->setParameter('user', $user);

        return $query->getResult() ?? [];

    }

    public function sizeOf()
    {
        $sql = 'SELECT count(q) FROM App\Entity\Question q ';
        $query = $this->getEntityManager()->createQuery($sql);

        return $query->getResult()[0][1] ?? 0;
    }
}
