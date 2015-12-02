<?php

namespace AG\UserBundle\Controller;


use AG\UserBundle\Entity\User;
use AG\UserBundle\Form\UserEditType;
use AG\UserBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends Controller
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Request
     */
    private $request;

    /**
     * @return array
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function indexAction()
    {
        $users = $this->em->getRepository('AGUserBundle:User')->findAll();

        return array(
            'users' => $users,
        );
    }

    /**
     * @return array
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function addAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $form = $this->createForm(new UserType($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')), $user);

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            $userManager->updateUser($user);
            $this->addFlash('success', 'Utilisateur crée avec succès.');

            return $this->redirect($this->generateUrl('ag_user_admin_index'));
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @param User $user
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function editAction(User $user)
    {
        $form = $this->createForm(new UserEditType($this->get('security.context')->isGranted('ROLE_SUPER_ADMIN')), $user);

        if ($this->request->isMethod('POST')) {
            if ($form->handleRequest($this->request)->isValid()) {
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($user);
                $this->addFlash('warning', 'Utilisateur mis à jour.');

                return $this->redirect($this->generateUrl('ag_user_admin_index'));
            }
        }
        return array(
            'form' => $form->createView(),
            'user' => $user,
        );
    }

    /**
     * @param User $user
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function removeAction(User $user)
    {
        $form = $this->createFormBuilder()->getForm();

        if ($this->request->isMethod('POST')) {
            if ($form->handleRequest($this->request)->isValid()) {
                $this->em->remove($user);
                $this->em->flush();
                $this->addFlash('danger', 'Utilisateur supprimé avec succès.');

                return $this->redirectToRoute('ag_user_admin_index');
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => $user,
        );
    }
}
