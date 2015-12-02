<?php

namespace AG\LasVegasBundle\Controller;

use AG\LasVegasBundle\Entity\Album;
use AG\LasVegasBundle\Entity\Photo;
use AG\LasVegasBundle\Form\PhotoEditType;
use AG\LasVegasBundle\Form\PhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\DateTime;

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
        $page = $this->request->query->get('page', 1);

        if ($page < 1) {
            throw $this->createNotFoundException("La page $page n'existe pas.");
        }

        $listPhotos = $this->em->getRepository('AGLasVegasBundle:Photo')->findAll();

        //Nombre de photos par page
        $nbPerPage = 16;

        $nbPages = ceil(count($listPhotos) / $nbPerPage);

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
            throw new AccessDeniedException('Vous ne pouvez pas modifier cette photo car elle ne vous appartient pas.');

        $form = $this->createForm(new PhotoEditType($this->isGranted('ROLE_SUPER_ADMIN')), $photo);

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
     * @Secure(roles="ROLE_ADMIN")
     */
    public function removeAction(Photo $photo)
    {
        if ($this->getUser() !== $photo->getAuthor()) {
            return new JsonResponse(array(
                'success' => 0,
                'error' => 'Cette photo ne vous appartient pas'
            ));
        }

        $this->em->remove($photo);
        $this->em->flush();

        return new JsonResponse(array(
            'success' => 1
        ));
    }

    /**
     * @param Photo $photo
     * @return JsonResponse
     * @Secure(roles="ROLE_ADMIN")
     */
    public function renameAction(Photo $photo)
    {
        if ($this->getUser() !== $photo->getAuthor()) {
            return new JsonResponse(array(
                'success' => 0,
                'error' => 'Cette photo ne vous appartient pas'
            ));
        }

        $name = $this->request->query->get('name', null);

        if (null == $name || trim($name) == "") {
            return new JsonResponse(array(
                'success' => 0,
                'error' => 'Veuillez spécifier un nom'
            ));
        }

        $photo->setName($name);
        $this->em->persist($photo);
        $this->em->flush();

        return new JsonResponse(array(
            'success' => 1
        ));
    }

    /**
     * @param Photo $photo
     * @return array
     * @Template
     */
    public function templateAction(Photo $photo)
    {
        return array(
            'photo' => $photo,
        );
    }

    /**
     * @param Photo $photo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Secure(roles="ROLE_ADMIN")
     */
    public function rotateRightAction(Photo $photo)
    {
        if ($this->getUser() !== $photo->getAuthor())
            throw new AccessDeniedException('Vous ne pouvez pas modifier cette photo car elle ne vous appartient pas.');

        $this->get('ag.image_rotator')->rotateRight($photo->getPath());

        $cacheManager = $this->get('liip_imagine.cache.manager');
        $cacheManager->resolve($photo->getWebPath(), 'my_thumbnail');
        $cacheManager->remove($photo->getWebPath());

        $referer = $this->request->headers->get('referer');
        $pos = strpos($referer, '=') + 1;
        $page = substr($referer, $pos);
        $page = ($page !== null && $pos > 1) ? $page : 1;

        $parameters = array(
            'id' => $photo->getAlbum()->getId()
        );
        if (1 !== $page) {
            $parameters['page'] = $page;
        }

        $response = new RedirectResponse($this->generateUrl('ag_album_show', $parameters));

        return $response;
    }

    /**
     * @param Photo $photo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Secure(roles="ROLE_ADMIN")
     */
    public function rotateLeftAction(Photo $photo)
    {
        if ($this->getUser() !== $photo->getAuthor())
            throw new AccessDeniedException('Vous ne pouvez pas modifier cette photo car elle ne vous appartient pas.');

        $this->get('ag.image_rotator')->rotateLeft($photo->getPath());

        $cacheManager = $this->get('liip_imagine.cache.manager');
        $cacheManager->resolve($photo->getWebPath(), 'my_thumbnail');
        $cacheManager->remove($photo->getWebPath());

        $referer = $this->request->headers->get('referer');
        $pos = strpos($referer, '=') + 1;
        $page = substr($referer, $pos);
        $page = ($page !== null && $pos > 1) ? $page : 1;

        $parameters = array(
            'id' => $photo->getAlbum()->getId()
        );
        if (1 !== $page) {
            $parameters['page'] = $page;
        }

        $response = new RedirectResponse($this->generateUrl('ag_album_show', $parameters));

        return $response;
    }
}
