<?php

namespace App\Controller;

use App\Business\SortBusiness;
use App\Entity\Question;
use App\Service\QuestionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/review")
 */
class QuestionController extends Controller
{
    const ARIANE_PIPE = " > ";

    /**
     * Lists all question entities.
     *
     * @Route("/", name="_review-question_index")
     * @param Request $request
     * @param QuestionService $questionService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(
        Request $request,
        QuestionService $questionService)
    {
        $subjectId = $request->query->get('subject');
        $topicId = $request->query->get('topic');
        $sourceId = $request->query->get('source');

        $questions = [];
        $nbr = 0;

        if ($topicId != null) {
            $questions = $questionService->findForTopic($topicId);
            $nbr = sizeof($questions);
        } else if ($subjectId != null) {
            $questions = $questionService->findForSubject($subjectId, null);
            $nbr = sizeof($questions);
        } else if ($sourceId != null) {
            if ($sourceId == 0) {
                $questions = $questionService->findAll();
                $nbr = sizeof($questions);
            } else {
                $questions = $questionService->findForSource($sourceId, null);
                $nbr = sizeof($questions);
            }
        } else {
            $nbr = $questionService->sizeOf();
        }

        SortBusiness::sortQuestionsByChapters($questions);

        return $this->render('site/review/index.html.twig', array(
            'questions' => $questions,
            'count' => $nbr
        ));
    }

    /**
     * Lists all question entities.
     *
     * @Route("/filter", name="_review-question_filter")
     * @param Request $request
     * @return JsonResponse
     */
    public function indexFilterAction(Request $request)
    {
        $target = $request->query->get('target');
        $id = $request->query->get('id');

        $filters = [];
        $data = [];

        $user = $this->getUser();

        if ($target != null) {
            switch ($target) {
                case "source":
                    $data = $this->get('source.service')->findAll();
                    break;
                case "subject":
                    $data = $this->get('subject.service')->findAllForSourceWithUser($id, $user);
                    break;
                case "topic":
                    $data = $this->get('topic.service')->findTopicForSubject($id);
                    break;

            }

            foreach ($data as $d) {
                array_push($filters, ["id" => $d->getId(), "name" => $d->getName()]);
            }
        }

        return new JsonResponse($filters);
    }

    /**
     * Finds and displays a question entity.
     *
     * @Route("/show/{id}", name="_review-question_show")
     * @param Question $question
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Question $question)
    {
        $headerInfo = $question->getTopic()->getSubject()->getName()
            . self::ARIANE_PIPE
            . $question->getTopic()->getName();

        return $this->render('site/review/show.html.twig', array(
            'question' => $question,
            'headerInfo' => $headerInfo
        ));
    }
}
