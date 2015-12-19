<?php

namespace site\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\CountryModel;
use repositories\builders\CountryRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryListController {
	
	function index(Application $app) {
		
		$crb = new CountryRepositoryBuilder();
		$crb->setSiteIn($app['currentSite']);
		$countries = $crb->fetchAll();
		
		
		return $app['twig']->render('site/countrylist/index.html.twig', array(
				'countries'=>$countries,
			));
		
	}
	
}


