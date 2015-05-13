<?php

namespace AG\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use AG\LasVegasBundle\Entity\Album;

/**
 * User
 *
 * @ORM\Table(name="las_vegas_user")
 * @ORM\Entity(repositoryClass="AG\UserBundle\Entity\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AG\LasVegasBundle\Entity\Album", mappedBy="author")
     */
    private $albums;

    public function __construct()
    {
        parent::__construct();
        $this->albums = new ArrayCollection;
    }


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add albums
     *
     * @param Album $albums
     * @return User
     */
    public function addAlbum(Album $albums)
    {
        $this->albums[] = $albums;

        return $this;
    }

    /**
     * Remove albums
     *
     * @param Album $albums
     */
    public function removeAlbum(Album $albums)
    {
        $this->albums->removeElement($albums);
    }

    /**
     * Get albums
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAlbums()
    {
        return $this->albums;
    }
}
