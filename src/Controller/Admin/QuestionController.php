<?php

namespace App\Controller\Admin;

use App\Business\ControllerUtils;
use App\Business\SortBusiness;
use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\Tag;
use App\Form\Question\QuestionForm;
use App\Service\QuestionService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Question controller.
 *
 * @Route("admin/question")
 */
class QuestionController extends Controller
{
    /**
     * Lists all question entities.
     *
     * @Route("/", name="_admin-question_index")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, QuestionService $service)
    {
        $subjectId = $request->query->get('subject');
        $topicId = $request->query->get('topic');
        $sourceId = $request->query->get('source');
        $tag = $request->query->get('tag');

        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        $questions = [];
        $nbr = 0;

        if ($topicId != null) {
            $questions = $isSuperAdmin
                ? $service->findForTopic($topicId, $tag)
                : $service->findForTopicByAdmin($topicId, $user);
            $nbr = sizeof($questions);
        } else if ($subjectId != null) {
            $questions = $isSuperAdmin
                ? $service->findForSubject($subjectId, $tag)
                : $service->findForSubjectByAdmin($subjectId, $user);

            $nbr = sizeof($questions);
        } else if ($sourceId != null) {
            $questions = $isSuperAdmin
                ? $service->findForSource($sourceId, $tag)
                : $service->findForSourceByAdmin($sourceId, $user);

            $nbr = sizeof($questions);
        } else {
            $nbr = $service->sizeOf();
        }

        SortBusiness::sortQuestionsByChapters($questions);

        return $this->render('Admin/question/index.html.twig', array(
            'questions' => $questions,
            'count' => $nbr,
        ));
    }


    /**
     * Lists all question entities.
     *
     * @Route("/filter", name="_admin-question_filter")
     */
    public function indexFilterAction(Request $request)
    {

        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        $target = $request->query->get('target');
        $id = $request->query->get('id');

        $filters = [];
        $data = [];

        $tags = [];

        if ($target != null) {
            switch ($target) {
                case "source":
                    $data = $isSuperAdmin
                        ? $this->get('source.service')->findAll(true)
                        : $this->get('source.service')->findAllByAdmin($user);

                    $tags = $isSuperAdmin
                        ? $this->get('tag.service')->findForSource($id)
                        : [];
                    break;

                case "subject":
                    $data = $isSuperAdmin
                        ? $this->get('subject.service')->findAllForSource($id, true)
                        : $this->get('subject.service')->findSubjectFromSourceForAdmin($id, $user);

                    $tags = $isSuperAdmin
                        ? $this->get('tag.service')->findForSource($id)
                        : [];
                    break;

                case "topic":
                    $data = $isSuperAdmin
                        ? $this->get('topic.service')->findTopicForSubject($id)
                        : $this->get('topic.service')->findTopicForSubjectByAdmin($id, $user);

                    $tags = $isSuperAdmin
                        ? $this->get('tag.service')->findForSubject($id)
                        : [];
                    break;

            }

            $tagsName = [];
            /* @var $t Tag */
            foreach ($tags as $t) {
                array_push($tagsName, $t->getValue());
            }

            foreach ($data as $d) {
                array_push($filters, ["id" => $d->getId(), "name" => $d->getName()]);
            }
        }

        return new JsonResponse([
            "filters" => $filters,
            "tags" => $tagsName
        ]);
    }

    /**
     * Creates a new question entity.
     *
     * @Route("/newnew", name="_question_new")
     */
    public function newQuestionAction(Request $request)
    {
        $question = new QuestionForm();
        $form = $this->createForm('AdminBundle\Form\Question\QuestionType', $question);
        $form->handleRequest($request);

        return $this->render('AdminBundle:question:newnew.html.twig', array(
            'question' => $question,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new question entity.
     *
     * @Route("/new", name="question_new", methods={"GET", "POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request)
    {
        $question = new Question();

        $questionId = $request->query->get('from');
        if ($questionId !== null) {
            /* @var $db Question */
            $db = $this->get('question.service')->findById($questionId, $this->getUser());

            $question->setTopic($db->getTopic());
            $question->setLabel($db->getLabel());
            $question->setCanonical($db->getCanonical());

            /* @var $q Answer */
            foreach ($db->getAnswers() as $aDb) {
                $a = new Answer();
                $a->setValue($aDb->getValue());
                $a->setGood($aDb->isGood());

                $question->getAnswers()->add($a);
            }

            $question->setExplain($db->getExplain());
        } else {

            $question->getAnswers()->add(new Answer());
            $question->getAnswers()->add(new Answer());
            $question->getAnswers()->add(new Answer());
            $question->getAnswers()->add(new Answer());

        }

        $topicId = $request->query->get('topic');
        if ($topicId != null && $topicId != 0) {
            $topic = $this->get('topic.service')->findById($topicId);
            if ($topic != null) $question->setTopic($topic);
        }

        $form = $this->createForm('AdminBundle\Form\QuestionType', $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($question->getAnswers() as $answer) {
                $answer->setQuestion($question);
                $em->persist($answer);
            }

            $user = $this->getUser();

            $question->getTopic()->getSubject()->setAuthorisedAdmins($user);
            $question->getTopic()->getSubject()->setAuthorisedUsers($user);

            $em->persist($question);
            $em->flush($question);

            return $this->redirectToRoute('question_show', array('id' => $question->getId()));
        }

        return $this->render('AdminBundle:question:new.html.twig', array(
            'question' => $question,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a question entity.
     *
     * @Route("/{id}", name="question_show")
     */
    public function showAction(Question $question)
    {

        // security, check if user has right to be here
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        if (!$isSuperAdmin && !ControllerUtils::hasQuestionRights($user, $question)) {
            return $this->redirectToRoute('_admin-question_index');
        }

        $deleteForm = $this->createDeleteForm($question);

        return $this->render('AdminBundle:question:show.html.twig', array(
            'question' => $question,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to delete a question entity.
     *
     * @param Question $question The question entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Question $question)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('question_delete', array('id' => $question->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Displays a form to edit an existing question entity.
     *
     * @Route("/{id}/edit", name="question_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Question $question)
    {
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        // security, check if user has right to be here
        if (!$isSuperAdmin && !ControllerUtils::hasQuestionRights($user, $question)) {
            return $this->redirectToRoute('_admin-question_index');
        }

        $deleteForm = $this->createDeleteForm($question);
        $editForm = $this->createForm('AdminBundle\Form\QuestionType', $question);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('question_show', array('id' => $question->getId()));
        }

        return $this->render('AdminBundle:question:edit.html.twig', array(
            'question' => $question,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a question entity.
     *
     * @Route("/{id}", name="question_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Question $question)
    {

        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        // security, check if user has right to be here
        if (!$isSuperAdmin && !ControllerUtils::hasQuestionRights($user, $question)) {
            return $this->redirectToRoute('_admin-question_index');
        }

        $form = $this->createDeleteForm($question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Delete the following
            $follows = $this->get('follow.service')->findQuestionAllUser($question->getId());
            if ($follows != null) {
                foreach ($follows as $follow) {
                    $em->remove($follow);
                }
            }

            // Delete the potential follow up
            $focus = $this->get('focus.service')->findQuestionForAllUser($question->getId());
            if ($focus != null) {
                foreach ($focus as $f) {
                    $em->remove($f);
                }
            }

            foreach ($question->getAnswers() as $answer) {
                $em->remove($answer);
            }

            $em->remove($question);
            $em->flush($question);
        }

        return $this->redirectToRoute('_admin-question_index');
    }
}
