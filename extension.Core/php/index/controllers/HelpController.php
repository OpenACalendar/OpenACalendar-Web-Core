<?php

namespace index\controllers;

use Silex\Application;
use index\forms\CreateForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\ContactSupportModel;
use repositories\SiteRepository;
use Symfony\Component\Form\FormError;
use repositories\builders\SiteRepositoryBuilder;
use repositories\CountryRepository;
use repositories\ContactSupportRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class HelpController {
	
	function index(Application $app) {
		return $app['twig']->render('index/help/index.html.twig', array());
	}
	
	function watch(Application $app) {
		return $app['twig']->render('index/help/watch.html.twig', array());
	}
	
	
	function personalcalendar(Application $app) {
		return $app['twig']->render('index/help/personalcalendar.html.twig', array());
	}
	
	
	function contribute(Application $app) {
		return $app['twig']->render('index/help/contribute.html.twig', array());
	}
	
	function recur(Application $app) {
		return $app['twig']->render('index/help/recur.html.twig', array());
	}
	
	function urlimport(Application $app) {
		return $app['twig']->render('index/help/urlimport.html.twig', array());
	}
	
	
	
}


