<?php

namespace App\Business;


use App\Entity\Question;
use App\Entity\Source;
use App\Entity\Subject;
use App\Entity\Topic;
use App\Entity\User;

class ControllerUtils
{

    static function hasSourceRights($user, $source)
    {
        /* @var $source Source */
        if ($source != null && $source->getSubjects() !== null) {
            foreach ($source->getSubjects() as $subject) {
                if (self::hasSubjectRights($user, $subject)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @var $user User
     * @var $subject Subject
     * @return bool
     */
    static function hasSubjectRights($user, $subject)
    {
        /* @var $admin User */
        /* @var $oneSubject Subject */
        if ($subject != null && $subject->getAuthorisedAdmins() != null) {
            foreach ($subject->getAuthorisedAdmins() as $admin) {
                if ($admin->getId() == $user->getId()) {
                    return true;
                    break;
                }
            }
        }

        return false;
    }

    /**
     * @var $user User
     * @var $topic Topic
     * @return bool
     */
    static function hasTopicRights($user, $topic)
    {
        $subject = $topic->getSubject();
        return self::hasSubjectRights($user, $subject);
    }

    /**
     * @var $user User
     * @var $question Question
     * @return bool
     */
    static function hasQuestionRights($user, $question)
    {
        if ($question != null && $question->getTopic() != null) {
            return self::hasTopicRights($user, $question->getTopic());
        }

        return false;
    }

}