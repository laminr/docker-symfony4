<?php

namespace App\Service;

use App\Entity\Follow;
use App\Entity\Question;
use App\Entity\Topic;
use App\Entity\User;
use App\Repository\TopicRepository;
use App\Service\BaseService;
use Doctrine\Common\Persistence\ObjectManager;

class TopicService extends BaseService
{
    /**
     * @var TopicRepository
     */
    private $repository;

    public function __construct(ObjectManager $em, TopicRepository $repository)
    {
        parent::__construct($em);
        $this->repository = $repository;
    }

    public function findAll(): array {
        return $this->repository->findAll();
    }

    public function findById($id) {
        return $this->repository->findOneById($id);
    }

    public function findTopicForSubject($subjectId) {
        return $this->repository->findTopicForSubject($subjectId);
    }

    public function findTopicForSubjectByAdmin($subjectId, $user) {
        return $this->repository->findTopicForSubjectByAdmin($subjectId, $user);
    }

    public function findTopicByAdmin($user) {
        return $this->repository->findTopicByAdmin($user);
    }

    public function alreadyExist(Topic $topic): bool {
        return ($this->findWithText($topic) != null);
    }

    public function findWithText(Topic $topic) {
        return $this->repository->findWithText($topic->getName());
    }

    /**
     * Save Project entity
     *
     * @param Topic $topic
     * @internal param Project $project
     */
    public function save(Topic $topic)
    {
        $this->persistAndFlush($topic);
    }
}