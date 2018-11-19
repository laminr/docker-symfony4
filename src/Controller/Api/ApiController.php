<?php

namespace App\Controller\Api;

use App\Converter\QuestionConvert;
use App\Converter\SubjectConvert;
use App\Converter\TopicConvert;
use App\Entity\Question;
use App\Entity\Source;
use App\Entity\Topic;
use App\Service\FocusService;
use App\Service\FollowService;
use App\Service\QuestionService;
use App\Service\SourceService;
use App\Service\SubjectService;
use App\Service\TopicService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use App\Business\LastCallBusiness;
use App\Entity\User;

use App\Entity\Focus;
use App\Entity\Follow;
use App\Dto\AtplResponse;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{

    /**
     * @Route("/", name="_api-homepage")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index(Request $request)
    {
        // replace this example code with whatever you need
        return $this->redirectToRoute('nelmio_api_doc_index');
    }

    /**
     * @Route("/source", name="_api-source")
     * @param Request $request
     * @return JsonResponse
     */
    public function source(Request $request, SourceService $service)
    {
        $lastCall = $request->query->get('last', 0);
        LastCallBusiness::checkFormat($lastCall);

//        $last = new \DateTime();
//        $last->setTimestamp($lastCall ?? 0);
        $last = null;

        /** @var Source[] $sources */
        $sources = $service->findAll(false, $last);
        $values = [];
        foreach ($sources as $source) {
            array_push($values, [
                'id' => $source->getId(),
                'name' => $source->getName(),
                'updated' => strtotime($source->getUpdated()->format('Y-m-d H:i:s'))
            ]);
        }

        $response = new AtplResponse("getSources");
        $response->setData($values);
        $response->setTotal(sizeof($sources));
        $response->setSuccess();

        return new JsonResponse($response->getResponse());
    }


    /**
     * @Route("/source/{id}", name="_api-source-subject")
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function subjectApi(Request $request, SubjectService $service, $id = 0)
    {
        $user = $this->getUser();

        if ($user == null) {
            // To be remove after Android Login in place
            $apiKey = $request->query->get('token');
            if ($apiKey != null) {
                $apiUser = $this->get('fos_user.user_manager')->findUserBy(['token' => $apiKey]);
                if ($apiKey != null) {
                    $user = $apiUser;
                }
            }
        }

        if ($user == null) {
            $token = $this->get('security.token_storage')->getToken();
            if ($token != null && $token->getUser() instanceof User) {
                $user = $token->getUser();
            }
        }

        $subjects = $service->findAllForSourceWithUser($id, $user);
        $follows = [];
        $focus = [];
        $means = [];

        if ($user != null) {
            $followsStat = $service->statistic($user->getId());
            $mapFollow = [];
            foreach ($followsStat as $f) {
                $mapFollow[$f['id']] = intval($f['nbr']);
                $means[$f['id']] = $f['meanDone'];
            }
            $follows = $mapFollow;

            $focusStat = $this->get('focus.service')->statisticStar($user->getId());
            foreach ($focusStat as $f) {
                $focus[$f['id']] = intval($f['nbr']);
            }
        }

        $lastCall = $request->query->get('last', 0);
        LastCallBusiness::checkFormat($lastCall);

//        $last = new \DateTime();
//        $last->setTimestamp($lastCall ?? 0);
        $last = null;

        $dto = SubjectConvert::toApiDto($subjects, $follows, $means, $focus, $last);

        $response = new AtplResponse("getSources");
        $response->setData($dto);
        $response->setTotal(sizeof($dto));
        $response->setSuccess();

        return new JsonResponse($response->getResponse());
    }

    /**
     * @Route("/subject", name="_api-subject")
     */
    public function subject()
    {
        $subjects = $this->get('subject.service')->findAllPublic();
        $values = [];
        foreach ($subjects as $subject) {

            $topicsDto = [];
            foreach ($subject->getTopics() as $topic) {
                array_push($topicsDto, TopicConvert::toDto($topic));
            }

            $data = [
                'id' => $subject->getId(),
                'name' => $subject->getName(),
                'topics' => $topicsDto
            ];

            array_push($values, $data);
        }

        $response = new AtplResponse("getSubjects");
        $response->setData($values);
        $response->setTotal(sizeof($subjects));
        $response->setSuccess();

        return new JsonResponse($response->getResponse());
    }

    /**
     * @Route("/subject/{id}/topic", name="_api-subject-topic")
     * @param int $id
     * @return JsonResponse
     */
    public function topic($id = 0)
    {
        $topics = $this->get('topic.service')->findTopicForSubject($id);
        $values = [];
        foreach ($topics as $topic) {
            $data = [
                'id' => $topic->getId(),
                'name' => $topic->getName(),
                'size' => sizeof($topic->getQuestions())
            ];

            array_push($values, $data);
        }

        $response = new AtplResponse("getTopics");
        $response->setData($values);
        $response->setTotal(sizeof($topics));
        $response->setSuccess();

        return new JsonResponse($response->getResponse());
    }


    /**
     * @Route("/topic/{id}", name="_api-topic")
     * @Route("/topic/{id}/full", name="_api-question-full")
     * @Route("/topic/{id}/full/star", name="_api-question-full-star")
     * @Route("/topic/{id}/star", name="_api-question-star")
     * @param Request $request
     * @param int $id
     * @param $_route
     * @return JsonResponse* @internal param int $subjectId
     * @internal param int $topicId
     * @internal param int $id
     */
    public function topicQuestion(
        Request $request,
        TopicService $topicService,
        QuestionService $questionService,
        $_route,
        $id = 0)
    {
        $user = $this->getUser();

        if ($user == null) {
            // To be remove after Android Login in place
            $apiKey = $request->query->get('token');
            if ($apiKey != null) {
                $apiUser = $this->get('fos_user.user_manager')->findUserBy(['token' => $apiKey]);
                if ($apiKey != null) {
                    $user = $apiUser;
                }
            }
        }

        if ($user == null) {
            $token = $this->get('security.token_storage')->getToken();
            if ($token != null && $token->getUser() instanceof User) {
                $user = $token->getUser();
            }
        }

        $async = strpos($_route, '-full') === false;
        $starFirst = strpos($_route, '-star') !== false;

        /** @var Topic $topic */
        $topic = $topicService->findById($id);

        $questions = $questionService->sortFollow($topic->getQuestions(), $user, $starFirst);

        // Suppression des questions du Topic
        foreach ($topic->getQuestions() as $question) {
            $topic->removeQuestion($question);
        }

        // Ajout des question dans l'ordre
        foreach ($questions as $question) {
            $topic->addQuestion($question);
        }

        $response = new AtplResponse("getQuestions");
        $response->setData(TopicConvert::toDtoWithAnswer($topic, $async));
        $response->setTotal(1);
        $response->setSuccess();

        return new JsonResponse($response->getResponse());
    }

    /**
     * @Route("/question/{id}", name="_api-question")
     * @param Request $request
     * @param QuestionService $questionService
     * @param FollowService $followService
     * @param FocusService $focusService
     * @param int $id
     * @return JsonResponse
     * @internal param int $id
     */
    public function question(
        Request $request,
        QuestionService $questionService,
        FollowService $followService,
        FocusService $focusService,
        $id = 0)
    {
        $user = $this->getUser();

        if ($user == null) {
            // To be remove after Android Login in place
            $apiKey = $request->query->get('token');
            if ($apiKey != null) {
                $apiUser = $this->get('fos_user.user_manager')->findUserBy(['token' => $apiKey]);
                if ($apiKey != null) {
                    $user = $apiUser;
                }
            }
        }

        if ($user == null) {
            $token = $this->get('security.token_storage')->getToken();

            if ($token != null && $token->getUser() instanceof User) {
                $user = $token->getUser();
            }
        }

        $userId = $user == null || $user->getId() != null ? 0 : $user->getId();

        /** @var Question $question */
        $question = $questionService->findById($id, $user);

        /**
         * because the Left join doesn't work
         */
        $followsDB = $followService->findQuestion($question->getId(), $userId);
        $focusDB = $focusService->findQuestion($question->getId(), $userId);

        $question->setFollow($followsDB);
        $question->setFocus($focusDB);
        /**
         * ----------------------------------------------------
         */

        $session = $request->cookies->get('PHPSESSID');

        $response = new AtplResponse("getOneQuestion : $session user is null=" . ($user == null));
        $response->setData(QuestionConvert::toDto($question));
        $response->setTotal(1);
        $response->setSuccess();

        return new JsonResponse($response->getResponse());
    }

    /**
     * @Route("/question/{id}/focus/{care}", name="_api-question-focus")
     * @param Request $request
     * @param QuestionService $questionService
     * @param FocusService $focusService
     * @param int $id
     * @param int $care
     * @return JsonResponse
     * @internal param int $id
     */
    public function focus(
        Request $request,
        QuestionService $questionService,
        FocusService $focusService,
        $id = 0,
        $care = -1)
    {
        $user = $this->getUser();

        if ($user == null) {
            // To be remove after Android Login in place
            $apiKey = $request->query->get('token');
            if ($apiKey != null) {
                $apiUser = $this->get('fos_user.user_manager')->findUserBy(['token' => $apiKey]);
                if ($apiKey != null) {
                    $user = $apiUser;
                }
            }
        }

        if ($user == null) {
            $token = $this->get('security.token_storage')->getToken();
            if ($token != null && $token->getUser() instanceof User) {
                $user = $token->getUser();
            }
        }

        $response = new AtplResponse("focusOneQuestion:$care");

        if ($user != null) {
            $isCare = null;

            if (($care == 0 || $care == 1) && $user != null) {

                $question = $questionService->findById($id, $user);

                $focus = new Focus();
                $focus->setQuestion($question);
                $focus->setUser($user);
                $focus->setCare($care == 0 ? false : true);

                $isCare = $focusService->updateOrCreate($focus);
            }

            $response = new AtplResponse("focusOneQuestion:$care");
            $response->setData(["focus" => $isCare]);
            $response->setTotal(1);
            $response->setSuccess();
        }

        return new JsonResponse($response->getResponse());
    }

    /**
     * @Route("/question/{id}/follow/{good}", name="_api-question-follow")
     * @param int $id
     * @param int $good
     * @return JsonResponse
     * @internal param int $good
     * @internal param int $care
     * @internal param int $id
     */
    public function follow(Request $request, $id = 0, $good = -1)
    {

        $user = $this->getUser();
        if ($user == null) {
            $apiKey = $request->query->get('token');
            if ($apiKey != null) {
                $apiUser = $this->get('fos_user.user_manager')->findUserBy(['token' => $apiKey]);
                if ($apiKey != null) {
                    $user = $apiUser;
                }
            }
        }

        if ($user == null) {
            $token = $this->get('security.token_storage')->getToken();
            if ($token != null && $token->getUser() instanceof User) {
                $user = $token->getUser();
            }
        }

        $response = new AtplResponse("followQuestion:$good");

        if (($good == 0 || $good == 1) && $user != null) {

            $question = $this->get('question.service')->findById($id, $user);

            $focus = new Follow($user, $question);

            if ($good) {
                $focus->setGood(1);
            } else {
                $focus->setWrong(1);
            }

            $this->get('follow.service')->updateOrCreate($focus, $user);

            $response->setData(QuestionConvert::toDto($question));
            $response->setTotal(1);
            $response->setSuccess();
        }

        return new JsonResponse($response->getResponse());
    }
}
