<?php

namespace Sbbs\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SbbsMainBundle:Default:index.html.twig', array('name' => $name));
    }
}
