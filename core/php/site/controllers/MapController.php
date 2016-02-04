<?php

namespace site\controllers;

use repositories\builders\VenueRepositoryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\builders\EventRepositoryBuilder;
use repositories\VenueRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MapController {

	
	protected $parameters = array();
	
	protected function build($countryCode, $areaSlug, $venueSlug, Request $request, Application $app) {
		$this->parameters = array('country'=>null,'area'=>null,'venue'=>null);
		
		if ($areaSlug) {
			$ar = new AreaRepository($app);
			$this->parameters['area'] = $ar->loadBySlug($app['currentSite'], $areaSlug);
		}
		
		if ($this->parameters['area']) {
			$cr = new CountryRepository($app);
			$this->parameters['country'] = $cr->loadById($this->parameters['area']->getCountryID());
		} else if ($countryCode) {
			$cr = new CountryRepository($app);
			$this->parameters['country'] = $cr->loadByTwoCharCode($countryCode);
		}

		if ($venueSlug) {
			$vr = new VenueRepository($app);
			$this->parameters['venue'] = $vr->loadBySlug($app['currentSite'], $venueSlug);
		}

		return true;
	}
	
	
	function index(Application $app, Request $request) {
		
		$this->build(
				isset($_GET['country']) ? $_GET['country'] : null,
				isset($_GET['area']) ? $_GET['area'] : null,
				isset($_GET['venue']) ? $_GET['venue'] : null,
				$request, $app);
		
		$vrb = new VenueRepositoryBuilder($app);
		$vrb->setSite($app['currentSite']);
		$vrb->setIncludeDeleted(false);
		$vrb->setMustHaveLatLng(true);
		
		$venues = $vrb->fetchAll();
		
		$this->parameters['venues'] = $venues;
		
		return $app['twig']->render('site/mapPage.html.twig', $this->parameters);
		
	}
	
}

