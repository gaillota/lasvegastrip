<?php

namespace AG\LasVegasBundle\Controller;

use AG\LasVegasBundle\Entity\Album;
use AG\LasVegasBundle\Form\AlbumType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class AlbumAdminController extends Controller
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
        return array(
            'listAlbums' => $this->em->getRepository('AGLasVegasBundle:Album')->findAll(),
        );
    }

    /**
     * @param Album $album
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function editAction(Album $album)
    {
        $form = $this->createForm(new AlbumType($this->isGranted(('ROLE_SUPER_ADMIN'))), $album);

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            $this->em->persist($album);
            $this->em->flush();
            $this->addFlash('warning', 'Album mise à jour.');

            return $this->redirectToRoute('ag_album_admin_index');
        }

        return array(
            'form' => $form->createView(),
            'album' => $album,
        );
    }

    /**
     * @param Album $album
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function removeAction(Album $album)
    {
        $form = $this->createFormBuilder()->getForm();

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            foreach ($album->getPhotos() as $photo) {
                $this->em->remove($photo);
            }
            $this->em->flush();

            $this->em->remove($album);
            $this->em->flush();
            $this->addFlash('danger', 'Album supprimé avec succès.');

            return $this->redirectToRoute('ag_album_admin_index');
        }

        return array(
            'form' => $form->createView(),
            'album' => $album
        );
    }
}
