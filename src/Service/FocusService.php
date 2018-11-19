<?php

namespace App\Service;

use App\Entity\Focus;
use App\Repository\FocusRepository;
use App\Service\BaseService;
use Doctrine\Common\Persistence\ObjectManager;

class FocusService extends BaseService
{

    /**
     * @var FocusRepository
     */
    private $repository;

    /**
     * FocusService constructor.
     * @param ObjectManager $em
     * @param FocusRepository $repository
     */
    public function __construct(ObjectManager $em, FocusRepository $repository)
    {
        parent::__construct($em);
        $this->repository = $repository;
    }

    public function findById($id)
    {
        return $this->repository->findOneById($id);
    }

    public function findQuestion($questionId, $userId)
    {
        return $this->repository->findOneBy([
            'question' => $questionId,
            'user' => $userId
        ]);
    }

    public function findQuestionForAllUser($questionId)
    {
        return $this->repository->findBy([
            'question' => $questionId
        ]);
    }

    public function updateOrCreate(Focus $focus)
    {
        $isCare = $focus->isCare();

        /** @var $focusDb Focus */
        $focusDb = $this->repository->findOneBy([
            'question' => $focus->getQuestion()->getId()
        ]);

        if ($focusDb != null) {
            // diff then update
            if ($focusDb->getCare() != $focus->getCare()) {
                $focusDb->setCare($focus->getCare());
            } // same then remove
            else {
                $this->em->remove($focusDb);
                $isCare = null;
            }

            $this->em->flush();
        } else {
            $this->persistAndFlush($focus);
        }

        return $isCare;
    }

    public function statistic($userId = 0)
    {
        return $this->repository->statistic($userId);
    }

    public function statisticStar($userId = 0)
    {
        return $this->repository->statisticStar($userId);
    }
}