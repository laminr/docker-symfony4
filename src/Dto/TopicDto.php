<?php

namespace App\Dto;


class TopicDto
{
    private $id;
    private $name;
    private $follow;
    private $focus;
    private $questions;
    private $meanDone;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getFollow()
    {
        return $this->follow;
    }

    /**
     * @param mixed $follow
     */
    public function setFollow($follow)
    {
        $this->follow = $follow;
    }

    /**
     * @return mixed
     */
    public function getFocus()
    {
        return $this->focus;
    }

    /**
     * @param mixed $focus
     */
    public function setFocus($focus)
    {
        $this->focus = $focus;
    }

    /**
     * @return mixed
     */
    public function getQuestions()
    {
        return $this->questions;
    }

    /**
     * @param mixed $questions
     */
    public function setQuestions($questions)
    {
        $this->questions = $questions;
    }

    /**
     * @return mixed
     */
    public function getMeanDone()
    {
        return $this->meanDone;
    }

    /**
     * @param mixed $meanDone
     */
    public function setMeanDone($meanDone)
    {
        $this->meanDone = $meanDone;
    }
}