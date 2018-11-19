<?php

namespace App\Service;

use App\Entity\Subject;
use App\Repository\SubjectRepository;
use App\Service\BaseService;
use Doctrine\Common\Persistence\ObjectManager;

class SubjectService  extends BaseService
{

    /**
     * @var SubjectRepository
     */
    private $repository;

    /**
     * SubjectService constructor.
     * @param ObjectManager $em
     * @param SubjectRepository $repository
     */
    public function __construct(ObjectManager $em, SubjectRepository $repository)
    {
        parent::__construct($em);
        $this->repository = $repository;
    }

    public function findAll(): array {
        return $this->repository->findAll();
    }

    public function findAllPublic(): array {
        return $this->repository->findBy(['public' => true]);
    }

    public function findAllForSource($sourceId = 0, $connected = false): array {
        $options = ['source' => $sourceId];
        if(!$connected) {
            $options['public'] = !$connected;
        }

        return $this->repository->findBy($options);
    }

    public function findSubjectForAdmin($user) {
        return $this->repository->findSubjectForAdmin($user);
    }

    public function findAllForSourceWithUser($sourceId = 0, $user = null): array {
        return $this->repository->findSubject($sourceId, $user);
    }

    public function findSubjectFromSourceForAdmin($sourceId = 0, $user = null): array {
        return $this->repository->findSubjectFromSourceForAdmin($sourceId, $user);
    }

    public function findById($id = 0): Subject {
        return $this->repository->findOneById($id);
    }

    public function alreadyExist(Subject $subject): bool {
        return ($this->findWithText($subject) != null);
    }

    public function findWithText(Subject $subject) {
        return $this->repository->findWithText($subject->getName());
    }

    /**
     * Save Project entity
     *
     * @param Subject $subject
     */
    public function save(Subject $subject)
    {
        $this->persistAndFlush($subject);
    }
}