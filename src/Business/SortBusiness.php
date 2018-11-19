<?php

namespace App\Business;


class SortBusiness {

    static function sortSources(&$sources = []) {
        if (sizeof($sources) != 0) {
            uasort($sources, function($a, $b) {
                // -1 means before/first
                //  1 means after/last
                // source
                $value = strnatcmp($a->getName(), $b->getName());
                return $value > 1 ? 1 : $value;
            });
        }
    }

    static function sortQuestionsByChapters(&$questions = []) {
        if (sizeof($questions) != 0) {
            uasort($questions, function($a, $b) {
                // -1 means before/first
                //  1 means after/last
                $topic1 = $a->getTopic();
                $topic2 = $b->getTopic();

                $subject1 = $topic1->getSubject();
                $subject2 = $topic2->getSubject();

                $source1 = $subject1->getSource()->getName();
                $source2 = $subject2->getSource()->getName();

                $value = 0;
                if($source1 == $source2) {
                    if ($subject1->getName() == $subject2->getName()) {
                        if ($topic1->getName() == $topic2->getName()) {
                            $value = $a->getLabel() < $b->getLabel() ? -1 : 1;
                        } else {
                            $value = $topic1->getName() < $topic2->getName() ? -1 : 1;
                        }
                    } else {
                        $value = $subject1 < $subject2 ? -1 : 1;
                    }
                } else {
                    $value = $source1 < $source2 ? -1 : 1;
                }

                return $value;
            });
        }
    }

    static function sortSubjects(&$subjects = []) {
        if (sizeof($subjects) != 0) {
            uasort($subjects, function($a, $b) {
                // -1 means before/first
                //  1 means after/last
                // subject
                $source1 = $a->getSource()->getName();
                $source2 = $b->getSource()->getName();

                $value = 0;
                if($source1 == $source2) {
                    $order = strnatcmp($a->getName(), $b->getName());
                    $value = $order > 1 ? 1 : $order;
                } else {
                    $value = $source1 < $source2 ? -1 : 1;
                }

                return $value;
            });
        }
    }

    static function sortTopicsBySubject(&$topics = []) {
        if (sizeof($topics) != 0) {
            uasort($topics, function($a, $b) {
                // -1 means before/first
                //  1 means after/last
                // subject
                $subject1 = $a->getSubject()->getName();
                $subject2 = $b->getSubject()->getName();

                $value = 0;
                if($subject1 == $subject2) {
                    $order = strnatcmp($a->getName(), $b->getName());
                    $value = $order > 1 ? 1 : $order;
                } else {
                    $value = $subject1 < $subject2 ? -1 : 1;
                }

                return $value;
            });
        }
    }

    static function sortSubjectToTopics(&$subjects = []) {
        if (sizeof($subjects) != 0) {
            self::sortSubjects($subjects);
            foreach ($subjects as $subject) {
                $it = $subject->getTopics()->getIterator();
                $it->uasort(function($a, $b) {
                    // -1 means before/first
                    //  1 means after/last
                    // iterator
                    $value = strnatcmp($a->getName(), $b->getName());
                    return $value > 1 ? 1 : $value;
                });
            }
        }
    }

}