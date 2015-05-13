<?php

namespace AG\LasVegasBundle\Controller;

use AG\LasVegasBundle\Entity\Album;
use AG\LasVegasBundle\Entity\Photo;
use AG\LasVegasBundle\Form\AlbumType;
use AG\LasVegasBundle\Form\PhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AlbumController extends Controller
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
     * @Template
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {
        return array(
            'listAlbums' => $this->em->getRepository('AGLasVegasBundle:Album')->myFindAll(),
        );
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function newAction()
    {
        $album = new Album();

        return $this->handleForm($album, 'ag_album_upload');
    }

    /**
     * @param Album $album
     * @return array
     * @Template
     * @Secure(roles="ROLE_USER")
     */
    public function showAction(Album $album)
    {
        return array(
            'album' => $album,
        );
    }

    /**
     * @param Album $album
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editAction(Album $album)
    {
        if ($this->getUser() !== $album->getAuthor())
            throw new AccessDeniedException('Vous ne pouvez pas modifier cet album car il ne vous appartient pas.');

        return $this->handleForm($album, 'ag_album_show');
    }

    /**
     * @param Album $album
     * @return array
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function uploadAction(Album $album)
    {
        $photo = new Photo();

        $photo->setAlbum($album);

        $form = $this->createForm(new PhotoType(),$photo);

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();

            $this->redirect($this->generateUrl('ag_album_upload', array(
                'id' => $album->getId()
            )));
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
     * @Secure(roles="ROLE_ADMIN")
     */
    public function removeAction(Album $album)
    {
        if ($this->getUser() !== $album->getAuthor())
            throw new AccessDeniedException('Vous ne pouvez pas supprimer cet album car il ne vous appartient pas.');

        $form = $this->createFormBuilder()->getForm();

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            $this->em->remove($album);
            $this->em->flush();

            return $this->redirect($this->generateUrl('ag_lasvegasbundle_home'));
        }

        return array(
            'album' => $album,
            'form' => $form->createView()
        );
    }

    /**
     * @return array
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function myAlbumsAction()
    {
        return array(
            'listAlbums' => $this->em->getRepository('AGLasVegasBundle:Album')->findBy(array('author' => $this->getUser()))
        );
    }

    private function handleForm(Album $album, $route)
    {
        $form = $this->createForm(new AlbumType(), $album);

        if ($this->request->isMethod('POST')) {
            if ($form->handleRequest($this->request)->isValid()) {
                $this->em->persist($album);
                $this->em->flush();

                return $this->redirect($this->generateUrl($route, array(
                    'id' => $album->getId(),
                )));
            }
        }

        return array(
            'form' => $form->createView(),
            'album' => $album
        );
    }
}
