<?php

namespace App\Controller\Admin;

use App\Business\ControllerUtils;
use App\Entity\Source;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Source controller.
 *
 * @Route("admin/source")
 */
class SourceController extends Controller
{
    /**
     * Lists all source entities.
     *
     * @Route("/", name="source_index")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        $sources = $isSuperAdmin
            ? $this->get('source.service')->findAll(true)
            : $this->get('source.service')->findAllByAdmin($user);

        return $this->render('AdminBundle:source:index.html.twig', array(
            'sources' => $sources,
        ));
    }

    /**
     * Creates a new source entity.
     *
     * @Route("/new", name="source_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $source = new Source();
        $form = $this->createForm('AdminBundle\Form\SourceType', $source);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($source);
            $em->flush($source);

            return $this->redirectToRoute('source_show', array('id' => $source->getId()));
        }

        return $this->render('AdminBundle:source:new.html.twig', array(
            'source' => $source,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a source entity.
     *
     * @Route("/{id}", name="source_show")
     */
    public function showAction(Source $source)
    {

        // security, check if user has right to be here
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');

        if (!$isSuperAdmin && !ControllerUtils::hasSourceRights($user, $source)) {
            return $this->redirectToRoute('source_index');
        }

        $deleteForm = $this->createDeleteForm($source);

        return $this->render('AdminBundle:source:show.html.twig', array(
            'source' => $source,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing source entity.
     *
     * @Route("/{id}/edit", name="source_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Source $source)
    {
        // security, check if user has right to be here
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        if (!$isSuperAdmin && !ControllerUtils::hasSourceRights($user, $source)) {
            return $this->redirectToRoute('source_index');
        }

        $deleteForm = $this->createDeleteForm($source);
        $editForm = $this->createForm('AdminBundle\Form\SourceType', $source);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('source_show', array('id' => $source->getId()));
        }

        return $this->render('AdminBundle:source:edit.html.twig', array(
            'source' => $source,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a source entity.
     *
     * @Route("/{id}", name="source_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Source $source)
    {
        // security, check if user has right to be here
        $user = $this->getUser();
        $isSuperAdmin = $user->hasRole('ROLE_SUPER_ADMIN');
        if (!$isSuperAdmin && !ControllerUtils::hasSourceRights($user, $source)) {
            return $this->redirectToRoute('source_index');
        }

        $form = $this->createDeleteForm($source);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($source);
            $em->flush($source);
        }

        return $this->redirectToRoute('source_index');
    }

    /**
     * Creates a form to delete a source entity.
     *
     * @param Source $source The source entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Source $source)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('source_delete', array('id' => $source->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
