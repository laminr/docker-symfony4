<?php

namespace App\Service;

use App\Entity\Follow;
use App\Entity\User;
use App\Repository\FollowRepository;
use App\Service\BaseService;
use Doctrine\Common\Persistence\ObjectManager;

class FollowService extends BaseService
{
    /* @var \AdminBundle\Repository\FollowRepository $repository */
    protected $repository;

    /**
     * FollowService constructor.
     * @param ObjectManager $em
     * @param FollowRepository $repository
     */
    public function __construct(ObjectManager $em, FollowRepository $repository)
    {
        parent::__construct($em);
        $this->repository = $repository;
    }

    public function findById($id)
    {
        return $this->repository->findOneById($id);
    }

    public function findQuestion($questionId, $userId = 0)
    {
        return $this->repository->findOneBy([
            'question' => $questionId,
            'user' => $userId
        ]);
    }

    public function findQuestionAllUser($questionId)
    {
        return $this->repository->findQuestionAllUser($questionId);
    }

    public function statistic($userId = 0)
    {
        return $this->repository->statistic($userId);
    }

    public function updateOrCreate(Follow $follow, User $user)
    {

        $followsDB = $this->findQuestion($follow->getQuestion()->getId(), $user->getId());

        if ($followsDB != null) {
            $isGood = $follow->getGood() == 1;
            $followsDB->increment($isGood);
            $this->em->flush();
        } else {
            // To update value until Trigger
            $this->persistAndFlush($follow);
        }

    }

}