<?php

namespace AG\LasVegasBundle\Controller;

use AG\LasVegasBundle\Entity\Photo;
use AG\LasVegasBundle\Form\PhotoEditType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class PhotoAdminController extends Controller
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
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function indexAction()
    {
        $page = $this->request->query->get('page', 1);

        if ($page < 1) {
            throw $this->createNotFoundException("La page $page n'existe pas.");
        }

        // Number of photos per page
        $nbPerPage = 5;

        $listPhotos = $this->em->getRepository('AGLasVegasBundle:Photo')->findAll();

        $nbPages = ceil(count($listPhotos)/$nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page $page n'existe pas.");
        }

        return array(
            'listPhotos' => $listPhotos,
            'page' => $page,
            'nbPerPage' => $nbPerPage,
            'nbPages' => $nbPages,
        );
    }

    /**
     * @param Photo $photo
     * @return JsonResponse
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function editAction(Photo $photo)
    {
        $form = $this->createForm(new PhotoEditType($this->isGranted('ROLE_SUPER_ADMIN')), $photo);

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();
            $this->addFlash('warning', 'Photo mise à jour.');

            return $this->redirectToRoute('ag_photo_admin_index');
        }

        return array(
            'form' => $form->createView(),
            'photo' => $photo,
        );
    }

    /**
     * @param Photo $photo
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function removeAction(Photo $photo)
    {
        $this->em->remove($photo);
        $this->em->flush();

        return new JsonResponse(array('success' => 1));
    }
}
