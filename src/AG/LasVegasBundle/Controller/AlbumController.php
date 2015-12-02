<?php

namespace AG\LasVegasBundle\Controller;

use AG\LasVegasBundle\Entity\Album;
use AG\LasVegasBundle\Entity\Photo;
use AG\LasVegasBundle\Form\AlbumType;
use AG\LasVegasBundle\Form\PhotoType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

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
        $page = $this->request->query->get('page', 1);

        if ($page < 1) {
            throw $this->createNotFoundException("La page $page n'existe pas.");
        }

        //Nombre d'albums par page
        $nbPerPage = 6;

        $listAlbums = $this->em->getRepository('AGLasVegasBundle:Album')->getAlbums($page, $nbPerPage);

        $nbPages = ceil(count($listAlbums)/$nbPerPage);

        if ($page > $nbPages && $page > 1) {
            throw $this->createNotFoundException("La page $page n'existe pas.");
        }

        return array(
            'listAlbums' => $listAlbums,
            'page' => $page,
            'nbPages' => $nbPages,
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
        $page = $this->request->query->get('page', 1);

        if ($page < 1) {
            throw $this->createNotFoundException("La page $page n'existe pas.");
        }

        //Nombre d'albums par page
        $nbPerPage = 12;

        $nbPages = ceil(count($album->getPhotos()) / $nbPerPage);

        if ($page > $nbPages) {
            throw $this->createNotFoundException("La page $page n'existe pas.");
        }

        $listPhotos = $this->em->getRepository('AGLasVegasBundle:Photo')->findBy(array(
            'album' => $album,
        ), array(
            'takenAt' => 'ASC'
        ));

        return array(
            'album' => $album,
            'listPhotos' => $listPhotos,
            'page' => $page,
            'nbPerPage' => $nbPerPage,
            'nbPages' => $nbPages,
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
        $photo = new Photo($album);

        $form = $this->createForm(new PhotoType(), $photo);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            $response = array(
                'success' => 0
            );

            if ($form->isValid()) {
                $this->em->persist($photo);
                $this->em->flush();

                $ext = pathinfo($photo->getPath(), PATHINFO_EXTENSION);

                if ($ext === 'jpg' || $ext === 'jpeg')
                    $this->get('ag.image_rotator')->rotate($photo->getPath());

                $response['success'] = 1;
                $response['id'] = $photo->getId();
                $response['template'] = $this->generateUrl('ag_photo_template', array(
                    'id' => $photo->getId(),
                ), UrlGenerator::ABSOLUTE_URL);
            }

            return new JsonResponse($response);
        }

        // Affiche les x dernières photos uploadées
        $nbPhotos = 8;

        $listPhotos = $this->em->getRepository('AGLasVegasBundle:Photo')->findBy(array(
            'author' => $this->getUser(),
            'album' => $album
        ), array(
            'id' => 'DESC'
        ), $nbPhotos);

        return array(
            'form' => $form->createView(),
            'album' => $album,
            'nbPhotos' => $nbPhotos,
            'listPhotos' => $listPhotos
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
            foreach($album->getPhotos() as $photo)
            {
                $this->em->remove($photo);
            }
            $this->em->flush();

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
     * @param Album $album
     * @return array
     * @Template
     * @Secure(roles="ROLE_ADMIN")
     */
    public function myPhotosAction(Album $album)
    {
        $listPhotos = $this->em->getRepository('AGLasVegasBundle:Photo')->findBy(array(
            'album' => $album,
            'author' => $this->getUser(),
        ), array(
            'takenAt' => 'ASC'
        ));

        return array(
            'album' => $album,
            'listPhotos' => $listPhotos,
        );
    }

    /**
     * @param Album $album
     * @return array
     * @Secure(roles="ROLE_USER")
     */
    public function downloadAction(Album $album)
    {
        $photos = $album->getPhotos();

        if (count($photos) === 0) {
            return $this->redirectToRoute('ag_album_show', array(
                'id' => $album->getId(),
            ));
        }

        $valid_photos = array();

        foreach ($album->getPhotos() as $photo) {
            if (file_exists($photo->getWebPath())) {
                $valid_photos[] = $photo;
            }
        }

        if (!count($valid_photos)) {
            throw new UnexpectedValueException("Aucune photo n'est valide pour créer l'archive.");
        }

        $zip = new \ZipArchive();
        $zipName = "Album-".strtolower(str_replace(' ', '_', $album->getName())).'-'.time().rand(1,1000).'.zip';
        $destination = __DIR__.'/../../../../web/zip/'.$zipName;

        if (true !== $zip->open($destination, \ZipArchive::CREATE)) {
            throw new UnexpectedValueException("Une erreur s'est produite lors de la création de l'archive.");
        }

        foreach ($valid_photos as $photo) {
            $zip->addFile($photo->getAbsolutePath(), $photo->getName());
        }

        $zip->close();

        $response = new Response();
        $response->headers->set('Content-Type', "application/zip");
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$zipName.'"');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Length', filesize($destination));
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        $response->setStatusCode(200);
        $response->setContent(file_get_contents($destination));

        unlink($destination);

        return $response;
    }


    /******************************************************************
     * @param Album $album
     * @param $route
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     ******************************************************************/
    private function handleForm(Album $album, $route)
    {
        $form = $this->createForm(new AlbumType($this->isGranted('ROLE_SUPER_ADMIN')), $album);

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
