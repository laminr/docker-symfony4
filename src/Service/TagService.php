<?php

namespace App\Service;

use App\Entity\Question;
use App\Repository\TagRepository;
use App\Service\BaseService;
use Doctrine\Common\Persistence\ObjectManager;

class TagService extends BaseService
{
    /**
     * @var TagRepository
     */
    private $repository;

    public function __construct(ObjectManager $em, TagRepository $repository)
    {
        parent::__construct($em);
        $this->repository = $repository;
    }

    public function findAll()
    {
        return $this->repository->findBy([], ['value' => 'ASC']);
    }

    public function findForSource($sourceId)
    {
        return $this->repository->findForSource($sourceId);
    }

    public function findForSubject($subjectId)
    {
        return $this->repository->findForSubject($subjectId);
    }

    public function findForTopic($topicId)
    {
        return $this->repository->findForTopic($topicId);
    }

    public function finForSourceForAdmin($sourceId, $user)
    {
        return $this->repository->findForSourceForAdmin($sourceId, $user);
    }

    public function findForSubjectForAdmin($subjectId, $user)
    {
        return $this->repository->findForSubjectForAdmin($subjectId, $user);
    }

    public function findForTopicForAdmin($topicId, $user)
    {
        return $this->repository->findForTopicForAdmin($topicId, $user);
    }
}