<?php

namespace AG\LasVegasBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;

class AdminController extends Controller
{
    /**
     * @return array
     * @Template
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function indexAction()
    {
        return array();
    }
}
