<?php

namespace App\Controller;

use App\Converter\SubjectConvert;
use App\Converter\TopicConvert;
use App\Entity\Question;
use App\Entity\Topic;
use App\Service\FocusService;
use App\Service\FollowService;
use App\Service\QuestionService;
use App\Service\SubjectService;
use App\Service\TopicService;
use App\Service\SourceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    const ARIANE_PIPE = " > ";

    /**
     * @Route("/", name="_homepage")
     * @param SourceService $sourceService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(SourceService $sourceService)
    {
        return $this->render('site/default/index.html.twig', [
            'sources' => $sourceService->findAll(($this->getUser() != null)),
        ]);
    }

    /**
     * @Route("/source/{id}", name="_source")
     * @param SourceService $sourceService
     * @param SubjectService $subjectService
     * @param FollowService $followService
     * @param FocusService $focusService
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subject(
        SourceService $sourceService,
        SubjectService $subjectService,
        FollowService $followService,
        FocusService $focusService,
        $id = 0)
    {
        $source = $sourceService->findById($id);
        $subjects = $subjectService->findAllForSourceWithUser($id, $this->getUser());
        $follows = [];
        $focus = [];
        $meanFollow = [];

        $user = $this->getUser();
        if ($user != null) {
            $followsStat = $followService->statistic($user->getId());
            $mapFollow = [];
            foreach ($followsStat as $f) {
                $mapFollow[$f['id']] = $f['nbr'];
                $meanFollow[$f['id']] = $f['meanDone'];
            }
            $follows = $mapFollow;

            $focusStat = $focusService->statisticStar($user->getId());
            $mapFocus = [];
            foreach ($focusStat as $f) {
                $mapFocus[$f['id']] = $f['nbr'];
            }
            $focus = $mapFocus;
        }

        $dto = SubjectConvert::toDto($subjects, $follows, $meanFollow, $focus);

        return $this->render('site/subject/all.html.twig', [
            'subjects' => $dto,
            'headerInfo' => $source != null ? $source->getName() ?? "" : ""
        ]);
    }

    /**
     * @Route("/topic/{id}", name="_topic-question")
     * @Route("/topic/{id}/full", name="_topic-question-full")
     * @Route("/topic/{id}/full/star", name="_topic-question-full-star")
     * @Route("/topic/{id}/star", name="_topic-question-star")
     * @param Request $request
     * @param TopicService $topicService
     * @param QuestionService $questionService
     * @param TopicConvert $topicConvert
     * @param $_route
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topicQuestion(
        Request $request,
        TopicService $topicService,
        QuestionService $questionService,
        $_route,
        $id = 0)
    {
        $user = $this->getUser();
        $async = strpos($_route, '-full') === false;
        $starFirst = strpos($_route, '-star') !== false;

        $rawOrder = $request->query->get('rawOrder');

        /** @var Topic $topic */
        $topic = $topicService->findById($id);

        if ($rawOrder == null) {
            $questions = $questionService->sortFollow($topic->getQuestions(), $user, $starFirst);

            // Supprression des questions du Topic
            foreach ($topic->getQuestions() as $question) {
                $topic->removeQuestion($question);
            }

            // Ajout des question dans l'ordre
            foreach ($questions as $question) {
                $topic->addQuestion($question);
            }
        }

        $source = $topic->getSubject()->getSource();
        $headerSubInfo = self::ARIANE_PIPE . $topic->getSubject()->getName(). self::ARIANE_PIPE . $topic->getName();

        return $this->render('site/question/question.html.twig', [
            'topic' => TopicConvert::toDtoWithAnswer($topic, $async),
            'headerInfo' => $source->getName(),
            'headerSubInfo' => $headerSubInfo,
            'headerId' => $source->getId(),
            'async' => $async
        ]);
    }

    /**
     * @Route("/topic/{id}/view", name="_topic-question-debug")
     * @Route("/topic/{id}/star/view", name="_topic-question-star-debug")
     * @param QuestionService $questionService
     * @param TopicService $topicService
     * @param TopicConvert $topicConvert
     * @param $_route
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topicQuestionDebug(
        QuestionService $questionService,
        TopicService $topicService,
        $_route,
        $id = 0)
    {
        $user = $this->getUser();
        $starFirst = $_route == '_topic-question-star';

        /** @var Topic $topic */
        $topic = $topicService->findById($id);

        $questions = $questionService->sortFollow($topic->getQuestions(), $user, $starFirst);

        // Supprression des questions du Topic
        foreach ($topic->getQuestions() as $question) {
            $topic->removeQuestion($question);
        }

        // Ajout des question dans l'ordre
        foreach ($questions as $question) {
            $topic->addQuestion($question);
        }

        return $this->render('site/question/question_debug.html.twig', [
            'topic' => TopicConvert::toDtoWithAnswer($topic, false),
            'async' => false
        ]);
    }

    /**
     * @Route("/question/{id}", name="_one-question")
     * @param QuestionService $questionService
     * @param TopicConvert $topicConvert
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function question(QuestionService $questionService, $id = 0)
    {
        /** @var Question $question */
        $question = $questionService->findById($id, $this->getUser());

        $topic = new Topic();
        $topic->setName($question->getTopic()->getName());
        $topic->setSubject($question->getTopic()->getSubject());
        $topic->addQuestion($question);

        return $this->render('question/question.html.twig', [
            'topic' => TopicConvert::toDtoWithAnswer($topic, false),
            'async' => false
        ]);
    }

}
