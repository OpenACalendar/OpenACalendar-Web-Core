<?php

namespace site\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\VenueModel;
use repositories\VenueRepository;
use repositories\builders\VenueRepositoryBuilder;
use repositories\builders\filterparams\VenueFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueListController {
	
	function index(Application $app) {
		
			
		$params = new VenueFilterParams();
		$params->set($_GET);
		$params->getVenueRepositoryBuilder()->setSite($app['currentSite']);
		$params->getVenueRepositoryBuilder()->setIncludeDeleted(false);
		$params->getVenueRepositoryBuilder()->setIncludeMediasSlugs(true);
		
		$venues = $params->getVenueRepositoryBuilder()->fetchAll();
		
		return $app['twig']->render('site/venuelist/index.html.twig', array(
				'venues'=>$venues,
				'venueListFilterParams'=>$params,
			));
		
	}
	
}


