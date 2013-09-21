<?php

namespace Sbbs\FrontBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

/**
 * Class DefaultController
 * @package Sbbs\FrontBundle\Controller
 *
 * @Route("/")
 */
class DefaultController extends Controller
{
	/**
	 * 掲示板TOPページ
	 *
	 * @return \Symfony\Component\HttpFoundation\Response
	 *
	 * @Route("/")
	 * @Method("GET")
	 * @Template()
	 * @Cache(expires="+2 days",public=true)
	 */
	public function indexAction()
    {
			return array();
    }
}
