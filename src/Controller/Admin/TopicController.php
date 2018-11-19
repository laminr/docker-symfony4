<?php

namespace App\Controller\Admin;

use App\Business\ControllerUtils;
use App\Business\SortBusiness;
use App\Entity\Topic;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Topic controller.
 *
 * @Route("admin/topic")
 */
class TopicController extends Controller
{
    /**
     * Lists all topic entities.
     *
     * @Route("/", name="topic_index")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $subjectId = $request->query->get('subject') ?? 0;

        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        $topics = [];
        if ($subjectId == 0) {
            $topics = $isSuperAdmin
                ? $this->get('topic.service')->findAll()
                : $this->get('topic.service')->findTopicByAdmin($user);
        } else {
            $topics = $isSuperAdmin
                ? $this->get('topic.service')->findTopicForSubject($subjectId)
                : $this->get('topic.service')->findTopicForSubjectByAdmin($subjectId, $user);
        }


        SortBusiness::sortTopicsBySubject($topics);
        return $this->render('AdminBundle:topic:index.html.twig', array(
            'topics' => $topics,
        ));
    }

    /**
     * Creates a new topic entity.
     *
     * @Route("/new", name="topic_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $topic = new Topic();
        $form = $this->createForm('AdminBundle\Form\TopicType', $topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($topic);
            $em->flush($topic);

            return $this->redirectToRoute('topic_show', array('id' => $topic->getId()));
        }

        return $this->render('AdminBundle:topic:new.html.twig', array(
            'topic' => $topic,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a topic entity.
     *
     * @Route("/{id}", name="topic_show")
     */
    public function showAction(Topic $topic)
    {

        // security, check if user has right to be here
        $user = $this->getUser();
        if (!ControllerUtils::hasTopicRights($user, $topic)) {
            return $this->redirectToRoute('topic_index');
        }

        $deleteForm = $this->createDeleteForm($topic);

        return $this->render('AdminBundle:topic:show.html.twig', array(
            'topic' => $topic,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing topic entity.
     *
     * @Route("/{id}/edit", name="topic_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Topic $topic)
    {
        // security, check if user has right to be here
        $user = $this->getUser();
        if (!ControllerUtils::hasTopicRights($user, $topic)) {
            return $this->redirectToRoute('topic_index');
        }

        $deleteForm = $this->createDeleteForm($topic);
        $editForm = $this->createForm('AdminBundle\Form\TopicType', $topic);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('topic_show', array('id' => $topic->getId()));
        }

        return $this->render('AdminBundle:topic:edit.html.twig', array(
            'topic' => $topic,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a topic entity.
     *
     * @Route("/{id}", name="topic_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Topic $topic)
    {
        // security, check if user has right to be here
        $user = $this->getUser();
        if (!ControllerUtils::hasTopicRights($user, $topic)) {
            return $this->redirectToRoute('topic_index');
        }

        $form = $this->createDeleteForm($topic);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($topic);
            $em->flush($topic);
        }

        return $this->redirectToRoute('topic_index');
    }

    /**
     * Creates a form to delete a topic entity.
     *
     * @param Topic $topic The topic entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Topic $topic)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('topic_delete', array('id' => $topic->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
