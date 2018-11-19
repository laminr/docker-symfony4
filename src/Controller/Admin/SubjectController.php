<?php

namespace App\Controller\Admin;

use App\Business\ControllerUtils;
use App\Business\SortBusiness;
use App\Entity\Subject;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Subject controller.
 *
 * @Route("admin/subject")
 */
class SubjectController extends Controller
{
    //  Securité
    //  https://www.remipoignon.fr/8-symfony-2-comment-verifier-le-role-d-un-utilisateur-en-respectant-la-hierarchie-des-roles

    /**
     * Lists all subject entities.
     *
     * @Route("/", name="subject_index")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        $subjects = $isSuperAdmin
            ? $this->get('subject.service')->findAll()
            : $this->get('subject.service')->findSubjectForAdmin($user);

        SortBusiness::sortSubjectToTopics($subjects);

        return $this->render('AdminBundle:subject:index.html.twig', array(
            'subjects' => $subjects,
        ));
    }

    /**
     * Creates a new subject entity.
     *
     * @Route("/new", name="subject_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $subject = new Subject();
        $form = $this->createForm('AdminBundle\Form\SubjectType', $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->getUser();

            // Check if admin has been already selected on the form
            $alreadyAdminIn = false;
            foreach ($subject->getAuthorisedAdmins() as $admin) {
                if ($admin == $user) {
                    $alreadyAdminIn = true;
                    break;
                }
            }
            if (!$alreadyAdminIn) $subject->getAuthorisedAdmins()->add($user);

            // Check if User has been already selected on the form
            $alreadyUserIn = false;
            foreach ($subject->getAuthorisedUsers() as $oneUser) {
                if ($oneUser == $user) {
                    $alreadyUserIn = true;
                    break;
                }
            }
            if (!$alreadyUserIn) $subject->getAuthorisedUsers()->add($user);

            $em = $this->getDoctrine()->getManager();
            $em->persist($subject);
            $em->flush($subject);

            return $this->redirectToRoute('subject_show', array('id' => $subject->getId()));
        }

        return $this->render('AdminBundle:subject:new.html.twig', array(
            'subject' => $subject,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a subject entity.
     *
     * @Route("/{id}", name="subject_show")
     */
    public function showAction(Subject $subject)
    {

        // security, check if user has right to be here
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        if (!$isSuperAdmin && !ControllerUtils::hasSubjectRights($user, $subject)) {
            return $this->redirectToRoute('subject_index');
        }

        $deleteForm = $this->createDeleteForm($subject);

        return $this->render('AdminBundle:subject:show.html.twig', array(
            'subject' => $subject,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing subject entity.
     *
     * @Route("/{id}/edit", name="subject_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Subject $subject)
    {

        // security, check if user has right to be here
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        if (!$isSuperAdmin && !ControllerUtils::hasSubjectRights($user, $subject)) {
            return $this->redirectToRoute('subject_index');
        }

        $deleteForm = $this->createDeleteForm($subject);
        $editForm = $this->createForm('AdminBundle\Form\SubjectType', $subject);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('subject_show', array('id' => $subject->getId()));
        }

        return $this->render('AdminBundle:subject:edit.html.twig', array(
            'subject' => $subject,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a subject entity.
     *
     * @Route("/{id}", name="subject_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Subject $subject)
    {
        // security, check if user has right to be here
        $user = $this->getUser();
        $allowed = ControllerUtils::hasSubjectRights($user, $subject);
        if (!$allowed) {
            return $this->redirectToRoute('subject_index');
        }

        if ($allowed) {
            $form = $this->createDeleteForm($subject);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($subject);
                $em->flush($subject);
            }
        }

        return $this->redirectToRoute('subject_index');
    }

    /**
     * Creates a form to delete a subject entity.
     *
     * @param Subject $subject The subject entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Subject $subject)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('subject_delete', array('id' => $subject->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
