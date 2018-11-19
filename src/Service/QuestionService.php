<?php

namespace App\Service;

use App\Entity\Follow;
use App\Entity\Question;
use App\Entity\User;
use App\Repository\QuestionRepository;
use App\Service\BaseService;
use Doctrine\Common\Persistence\ObjectManager;

class QuestionService extends BaseService
{
    /**
     * @var QuestionRepository
     */
    private $repository;

    public function __construct(ObjectManager $em, QuestionRepository $repository)
    {
        parent::__construct($em);
        $this->repository = $repository;
    }

    public function alreadyExist(Question $question): bool
    {
        return ($this->findWithText($question) != null);
    }

    public function findWithText(Question $question)
    {
        return $this->repository->findWithText($question->getCanonical());
    }

    public function findAll()
    {
        return $this->repository->findBy([], ['topic' => 'ASC']);

    }

    public function sizeOf()
    {
        return $this->repository->sizeOf();
    }

    public function findForSource($sourceId, $tag)
    {
        return $this->repository->findForSource($sourceId, $tag);
    }

    public function findForSourceByAdmin($sourceId, $user)
    {
        return $this->repository->findForSourceByAdmin($sourceId, $user);
    }

    public function findForSubject($subjectId, $tag)
    {
        return $this->repository->findForSubject($subjectId, $tag);
    }

    public function findForSubjectByAdmin($subjectId, $user)
    {
        return $this->repository->findForSubjectByAdmin($subjectId, $user);
    }


    public function findForTopic($topicId, $tag = null)
    {
        return $this->repository->findForTopic($topicId, $tag);
    }

    public function findForTopicByAdmin($topicId, $user)
    {
        return $this->repository->findForTopicByAdmin($topicId, $user);
    }

    public function findById($id, $user)
    {
        $result = null;

        if ($user != null) {
            $result = $this->repository->findOneByIdWithUser($id, $user);
        } else {
            $result = $this->repository->findOneById($id);
        }

        return $result;
    }


    /**
     * @param Question[] $questions
     * @param User $user
     * @param bool $startFirst
     * @return array
     */
    public function sortFollow($questions = [], $user = null, $startFirst = false)
    {
        /** @var Follow[] $followGood */
        $followGood = [];

        /** @var Follow[] $followEquals */
        $followEquals = [];

        /** @var Follow[] $followWrong */
        $followWrong = [];

        $importantGood = [];
        $importantWrong = [];

        $dontCare = [];
        $notDone = [];

        // shuffle first the raw order of questions
        $q = $questions->toArray();
        shuffle($q);

        /** @var Question $question */
        foreach ($q as $question) {

            // removing focus & follow if not the current user
            // Setting False not null, if not is considered
            if ($user == null) {
                $question->setFocus(null);
                $question->setFollow(null);
            } else {
                // si pas des focus/follow du user courant on les enlèves
                if ($question->getFocus() != null && $question->getFocus()->getUser() != $user) {
                    $question->setFocus(null);
                }
                if ($question->getFollow() != null && $question->getFollow()->getUser() != $user) {
                    $question->setFollow(null);
                }
            }

            $hasFocus = $question->getFocus() != null;

            // dont care
            if ($hasFocus && !$question->getFocus()->getCare()) {
                array_push($dontCare, $question);
            }
            // Care or not + w/ follow
            else if ($question->getFollow() != null) {
                // focus
                if ($hasFocus) {
                    // Important: Good > Wrong
                    if ($question->getFollow()->getGood() > $question->getFollow()->getWrong()) {
                        array_push($importantGood, $question);
                    }
                    // Important Wrong > Good or Equals
                    else {
                        array_push($importantWrong, $question);
                    }
                }
                // no focus
                else {

                    // Equals
                    if ($question->getFollow()->getGood() == $question->getFollow()->getWrong()) {
                        array_push($followEquals, $question);
                    }
                    // Good > Wrong
                    else  if ($question->getFollow()->getGood() >= $question->getFollow()->getWrong()) {
                        array_push($followGood, $question);
                    }
                    // Wrong > Good
                    else {
                        array_push($followWrong, $question);
                    }
                }
            } // Not Done & Focus++
            else {
                // Care or not, it has not been done!
                array_push($notDone, $question);
            }
        }

        self::sortWrong($followWrong);
        self::sortWrong($followEquals);
        self::sortGood($followGood);

        self::sortWrong($importantWrong);
        self::sortGood($importantGood);

        $data = [];

        if ($startFirst) {
            /***
             * Order:
             * - important question - Wrong
             * - important question - Good
             * - not done
             * - wrong answer DESC
             * - good answer ASC
             * - dont care question
             */
            $data = array_merge($data, $importantWrong);
            $data = array_merge($data, $importantGood);
            $data = array_merge($data, $notDone);
            $data = array_merge($data, $followWrong);
            $data = array_merge($data, $followEquals);
            $data = array_merge($data, $followGood);
            $data = array_merge($data, $dontCare);

        } else {
            /***
             * Order:
             * - not done
             * - wrong answer DESC
             * - important question - Wrong
             * - important question - Good
             * - good answer ASC
             * - dont care question
             */
            $data = array_merge($data, $notDone);
            $data = array_merge($data, $followWrong);
            $data = array_merge($data, $importantWrong);
            $data = array_merge($data, $importantGood);
            $data = array_merge($data, $followEquals);
            $data = array_merge($data, $followGood);
            $data = array_merge($data, $dontCare);
        }

        return $data;
    }

    private static function sortGood(&$table) {

        /***
         * @var Question $a
         * @var Question $b
         * Sort Good answer - ASC in general
         * /!\ Sort function take into account getCare question but curently in an another array !!!
         * getCare question are put before other question in ASC Order
         * Regular question are then in ASC order
         */
        uasort($table, function ($a, $b) {
            /** @var Question $a */
            /** @var Question $b */

            // -1 means before/first
            //  1 means after/last
            $aStar = ($a->getFocus() != null && $a->getFocus()->getCare());
            $bStar = ($b->getFocus() != null && $b->getFocus()->getCare());

            $aGood = $a->getFollow()->getGood();
            $bGood = $b->getFollow()->getGood();

            // A is care, B is regular
            if ($aStar && !$bStar) {
                return -1; // A before (-1 = A before)
            } // B is care
            else if (!$aStar && $bStar) {
                return 1; // B before (1 = A after)
            } // All care
            else if ($aStar && $bStar) {

                if ($aGood == $bGood) {
                    $aWrong = $a->getFollow()->getWrong();
                    $bWrong = $b->getFollow()->getWrong();

                    if ($aWrong == $bWrong) {
                        return 0;
                    }

                    return $aWrong < $bWrong ? 1 : -1; // A smaller is at the end (DESC)
                }

                return $aGood < $bGood ? -1 : 1; // A smaller is at start (ASC)
            }

            // no care at all
            if ($aGood == $bGood) {
                return 0;
            }

            return $aGood < $bGood ? -1 : 1; // A smaller is at start (ASC)
        });
    }

    private static function sortWrong(&$table) {
        /***
         * Sort Wrong answer - DESC in general
         * getCare question are put before other question in DESC Order
         * Regular question are then in DESC order
         */
        usort($table, function ($a, $b) {
            /** @var Question $a */
            /** @var Question $b */

            // -1 means before
            //  1 means after

            $aStar = ($a->getFocus() != null && $a->getFocus()->getCare());
            $bStar = ($b->getFocus() != null && $b->getFocus()->getCare());

            $aWrong = $a->getFollow()->getWrong();
            $bWrong = $b->getFollow()->getWrong();

            // A is care, B is regular
            if ($aStar && !$bStar) {
                return -1; // A before (-1 = A before)
            } // A regular, B is care
            else if (!$aStar && $bStar) {
                return 1; // B before (1 = A after)
            } // All care
            else if ($aStar && $bStar) {
                if ($aWrong == $bWrong) {
                    $aGood = $a->getFollow()->getGood();
                    $bGood = $b->getFollow()->getGood();

                    if ($aGood == $bGood) {
                        return 0;
                    }

                    return $aGood > $bGood ? 1 : -1; // A bigger is at the end (DESC)
                }

                return $aWrong < $bWrong ? 1 : -1; // A smaller is at the end (DESC)
            }

            // no care at all
            if ($aWrong == $bWrong) {
                return 0;
            }

            return $aWrong < $bWrong ? 1 : -1; // A smaller is at the end (DESC)
        });
    }

}