<?php

namespace App\Converter;

use AdminBundle\Entity\Focus;
use AdminBundle\Entity\Question;
use AdminBundle\Entity\Topic;
use App\Dto\SubjectDto;
use App\Dto\TopicDto;
use App\Entity\Subject;
use Symfony\Component\Intl\Data\Util\ArrayAccessibleResourceBundle;

class SubjectConvert
{

    /**
     * @param array $subjects
     * @param array $follows
     * @param array $meanDone
     * @param array $focus
     * @return array
     */
    public static function toDto($subjects = [], $follows = [], $meanDone = [], $focus = []) {
        $dto = [];

        /* @var $subject Subject */
        foreach ($subjects as $subject) {
            $s = new SubjectDto();
            $s->setId($subject->getId());
            $s->setName($subject->getName());

            $topics = [];
            /* @var $topic Topic */
            foreach ($subject->getTopics() as $topic) {
                $t = new TopicDto();
                $t->setId($topic->getId());
                $t->setName($topic->getName());
                $t->setQuestions(sizeof($topic->getQuestions()));

                if (array_key_exists($topic->getId(), $follows)) {
                    $t->setFollow($follows[$topic->getId()]);
                }

                if (array_key_exists($topic->getId(), $meanDone)) {
                    $t->setMeanDone(round($meanDone[$topic->getId()], 1));
                }

                if (array_key_exists($topic->getId(), $focus)) {
                    $t->setFocus($focus[$topic->getId()]);
                }

                array_push($topics, $t);
            }

            $s->setTopics($topics);

            array_push($dto, $s);
        }

        return $dto;
    }

    /**
     * @param array $subjects
     * @param array $follows
     * @param array $meanDone
     * @param array $focus
     * @param null $last
     * @return array
     */
    public static function toApiDto($subjects = [], $follows = [], $meanDone = [], $focus = [], $last = null): array {
        $dto = [];

        /* @var $subject Subject */
        foreach ($subjects as $subject) {
            $s = [
                "id" => $subject->getId(),
                "name" => $subject->getName(),
                "updated" => strtotime($subject->getUpdated()->format('Y-m-d H:i:s'))
            ];

            $topics = [];
            /* @var $topic Topic */
            foreach ($subject->getTopics() as $topic) {
                if ($topic->getUpdated() > $last) {
                    $t = [
                        "id" => $topic->getId(),
                        "name" => $topic->getName(),
                        "questions" => sizeof($topic->getQuestions()),
                        "updated" => strtotime($topic->getUpdated()->format('Y-m-d H:i:s'))
                    ];

                    if (array_key_exists($topic->getId(), $follows)) {
                        $t["follow"] = $follows[$topic->getId()];
                    }

                    if (array_key_exists($topic->getId(), $meanDone)) {
                        $t["mean"] = round($meanDone[$topic->getId()], 1);
                    }

                    if (array_key_exists($topic->getId(), $focus)) {
                        $t["focus"] = $focus[$topic->getId()];
                    }

                    array_push($topics, $t);
                }
            }

            $hasTopics = sizeof($topics) > 0;
            $putSubject = $subject->getUpdated() > $last;

            // if no topics don't put the empty array
            if ($hasTopics) {
                $s["topics"] = $topics;
            }

            if ($hasTopics || $putSubject) {
                array_push($dto, $s);
            }
        }

        return $dto;
    }
}