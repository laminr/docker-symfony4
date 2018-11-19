<?php

namespace App\Converter;

use App\Entity\Focus;
use App\Entity\Question;
use App\Entity\Topic;
use Symfony\Component\Intl\Data\Util\ArrayAccessibleResourceBundle;

class TopicConvert
{

    public static function toDto(Topic $topic)
    {
        return [
            'id' => $topic->getId(),
            'name' => $topic->getName()
        ];
    }

    public static function toDtoWithAnswer(Topic $topic, $onlyAnswerId = false): array
    {
        $questions = [];

        foreach ($topic->getQuestions() as $question) {

            if ($onlyAnswerId) {
                array_push($questions, $question->getId());
            } else {
                $qDto = QuestionConvert::toDto($question);
                array_push($questions, $qDto);
            }
        }

        $subject = [
            "id" => $topic->getSubject()->getId(),
            "name" => $topic->getSubject()->getName(),
            'sourceId' => $topic->getSubject()->getSource()->getId()
        ];

        $topicDto = [
            'id' => $topic->getId(),
            'subject' => $subject,
            'name' => $topic->getName(),
            'questions' => $questions,
            'size' => sizeof($questions)
        ];

        foreach ($topic->getSubject()->getTopics() as $t) {
            if ($t->getId() < $topic->getId()) {
                $topicDto["previous"] = $t->getId();
            }
            if ($t->getId() > $topic->getId()) {
                $topicDto["next"] = $t->getId();
                break;
            }
        }

        return $topicDto;
    }
}