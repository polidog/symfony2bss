<?php

namespace Sbbs\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('SbbsFrontBundle:Default:index.html.twig', array('name' => $name));
    }
}
