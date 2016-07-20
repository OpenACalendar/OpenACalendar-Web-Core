<?php

namespace siteapi1\controllers;

use api1exportbuilders\EventListCSVBuilder;
use api1exportbuilders\ICalEventIdConfig;
use repositories\builders\AreaRepositoryBuilder;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\CountryModel;
use models\EventModel;
use repositories\CountryRepository;
use repositories\builders\GroupRepositoryBuilder;
use repositories\EventRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use api1exportbuilders\EventListICalBuilder;
use api1exportbuilders\EventListJSONBuilder;
use api1exportbuilders\EventListJSONPBuilder;
use api1exportbuilders\EventListATOMBeforeBuilder;
use api1exportbuilders\EventListATOMCreateBuilder;

use repositories\builders\filterparams\EventFilterParams;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class CountryController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array();
		
		$gr = new CountryRepository($app);
		$this->parameters['country'] = $gr->loadByTwoCharCode($slug);
		if (!$this->parameters['country']) {
			return false;
		}
		
		// TODO could check this country is or was valid for this site?
		
		return true;
	}
	
	
	

	function eventsIcal($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Country does not exist.");
		}
		
		$ical = new EventListICalBuilder($app, $app['currentSite'], $app['currentTimeZone'], $this->parameters['country']->getTitle(), new ICalEventIdConfig($request->get('eventidconfig'), $request->server->all()));
		$ical->getEventRepositoryBuilder()->setCountry($this->parameters['country']);
		$ical->build();
		return $ical->getResponse();
				
	}

	function eventsJson($slug, Request $request, Application $app) {


		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Country does not exist.");
		}

		
		$json = new EventListJSONBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$json->getEventRepositoryBuilder()->setCountry($this->parameters['country']);
		$json->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$json->build();
		return $json->getResponse();
				
	}	

	function eventsJsonp($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Country does not exist.");
		}

		
		$jsonp = new EventListJSONPBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$jsonp->getEventRepositoryBuilder()->setCountry($this->parameters['country']);
		$jsonp->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$jsonp->build();
		if (isset($_GET['callback'])) $jsonp->setCallBackFunction($_GET['callback']);
		return $jsonp->getResponse();
				
	}	
	function eventsCSV($slug, Request $request, Application $app) {

		$ourRequest = new \Request($request);

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Country does not exist.");
		}


		$csv = new EventListCSVBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$csv->getEventRepositoryBuilder()->setCountry($this->parameters['country']);
		$csv->setIncludeEventMedias($ourRequest->getGetOrPostBoolean("includeMedias",false));
		$csv->build();
		return $csv->getResponse();

	}

	function eventsAtomBefore($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Country does not exist.");
		}

		$days = isset($_GET['days']) ? $_GET['days'] : null;
		$atom = new EventListATOMBeforeBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setDaysBefore($days);
		$atom->setTitle($this->parameters['country']->getTitle());
		$atom->getEventRepositoryBuilder()->setCountry($this->parameters['country']);
		$atom->build();
		return $atom->getResponse();
	}	
	

	function eventsAtomCreate($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Country does not exist.");
		}

		
		$atom = new EventListATOMCreateBuilder($app, $app['currentSite'], $app['currentTimeZone']);
		$atom->setTitle($this->parameters['country']->getTitle());
		$atom->getEventRepositoryBuilder()->setCountry($this->parameters['country']);
		$atom->build();
		return $atom->getResponse();
	}

    function areasJson($slug, Request $request, Application $app) {

        if (!$this->build($slug, $request, $app)) {
            $app->abort(404, "Country does not exist.");
        }

        $areaRepoBuilder = new AreaRepositoryBuilder($app);
        $areaRepoBuilder->setCountry($this->parameters['country']);
        $areaRepoBuilder->setSite($app['currentSite']);

        if (isset($_GET['includeDeleted'])) {
            if (in_array(strtolower($_GET['includeDeleted']),array('yes','on','1'))) {
                $areaRepoBuilder->setIncludeDeleted(true);
            } else if (in_array(strtolower($_GET['includeDeleted']),array('no','off','0'))) {
                $areaRepoBuilder->setIncludeDeleted(false);
            }
        }
        if (isset($_GET['titleSearch']) && trim($_GET['titleSearch'])) {
            $areaRepoBuilder->setTitleSearch($_GET['titleSearch']);
        }

        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $areaRepoBuilder->setLimit(intval($_GET['limit']));
        } else {
            $areaRepoBuilder->setLimit($app['config']->api1AreaListLimit);
        }

        $out = array();

        foreach($areaRepoBuilder->fetchAll() as $area) {
            $out[] = array(
                'slug'=>$area->getSlug(),
                'title'=>$area->getTitle(),
            );
        }

        $response = new Response(json_encode(array('data'=>$out)));
        $response->headers->set('Content-Type', 'application/json');
        return $response;



    }

	
	
}


