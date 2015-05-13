<?php

namespace AG\LasVegasBundle\Controller;

use AG\LasVegasBundle\Entity\Album;
use AG\LasVegasBundle\Entity\Photo;
use AG\LasVegasBundle\Form\PhotoEditType;
use AG\LasVegasBundle\Form\PhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PhotoController extends Controller
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
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {
        return array(
            'listPhotos' => $this->em->getRepository('AGLasVegasBundle:Photo')->findAll(),
        );
    }

    /**
     * @param Album $album
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function newAction(Album $album)
    {
        $photo = new Photo();

        $photo->setAlbum($album);

        $form = $this->createForm(new PhotoType(), $photo);

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();

            return $this->redirect($this->generateUrl('ag_photo_new', array(
                'id' => $album->getId(),
            )));
        }

        return array(
            'form' => $form->createView(),
            'album' => $album,
        );
    }

    /**
     * @param Photo $photo
     * @return array
     * @Template
     * @Secure(roles="ROLE_USER")
     */
    public function showAction(Photo $photo)
    {
        return array(
            'photo' => $photo,
        );
    }

    /**
     * @param Photo $photo
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function editAction(Photo $photo)
    {
        if ($this->getUser() !== $photo->getAuthor())
            throw new AccessDeniedException('Vous ne pouvez pas modifier cet album car il ne vous appartient pas.');

        $form = $this->createForm(new PhotoEditType(), $photo);

        if ($this->request->isMethod('POST') && $form->handleRequest($this->request)->isValid()) {
            $this->em->persist($photo);
            $this->em->flush();

            return $this->redirect($this->generateUrl('ag_photo_show', array(
                'id' => $photo->getId(),
            )));
        }

        return array(
            'form' => $form->createView(),
            'photo' => $photo,
        );
    }

    /**
     * @param Photo $photo
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function removeAction(Photo $photo)
    {
        if ($this->getUser() !== $photo->getAuthor())
            throw new AccessDeniedException('Vous ne pouvez pas modifier cette photo car elle ne vous appartient pas.');

        $this->em->remove($photo);
        $this->em->flush();

        return new JsonResponse('ok');
    }
}
