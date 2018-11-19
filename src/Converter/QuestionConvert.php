<?php

namespace App\Converter;

use App\Entity\Focus;
use App\Entity\Question;
use App\Entity\Topic;
use Symfony\Component\Intl\Data\Util\ArrayAccessibleResourceBundle;

class QuestionConvert
{
    
    public static function toDto(Question $question): array
    {
        $answers = [];
        foreach ($question->getAnswers() as $answer) {
            array_push($answers, [
                'id' => $answer->getId(),
                'value' => $answer->getValue(),
                'good' => $answer->getGood() ? true : false,
            ]);
        }

        $dto = [
            'id' => $question->getId(),
            'topic' => $question->getTopic()->getId(),
            'label' => $question->getLabel(),
            'answers' => $answers,
            'img' => $question->getRefImg()
        ];

        if ($question->getExplain() != "") {
            $dto['explain'] = $question->getExplain();
        }

        if ($question->getFocus() != null) {
            $dto['focus'] = $question->getFocus()->isCare();
        }

        if ($question->getFollow() != null) {
            $followDto = [
                "user" => $question->getFollow()->getUser()->getUsername(),
                "good" => $question->getFollow()->getGood(),
                "wrong" => $question->getFollow()->getWrong(),
            ];
            $dto['follow'] = $followDto;
        }

        return $dto;
    }
}