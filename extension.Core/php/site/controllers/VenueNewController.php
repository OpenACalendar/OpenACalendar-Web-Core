<?php

namespace site\controllers;

use Silex\Application;
use site\forms\VenueNewForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use models\VenueModel;
use models\AreaModel;
use repositories\VenueRepository;
use repositories\AreaRepository;
use repositories\CountryRepository;
use repositories\builders\AreaRepositoryBuilder;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueNewController {
	
	protected $parameters = array();
	
	function newVenue(Request $request, Application $app) {
		$areaRepository = new AreaRepository();
		$countryRepository = new CountryRepository();
					
		$venue = new VenueModel();
		
		
		$this->parameters = array('country'=>null,'parentAreas'=>array(),'area'=>null,'childAreas'=>array(),'startAreaBrowserFromScratch'=> true);
		
		
		if (isset($_GET['area_id'])) {
			$ar = new AreaRepository();
			$this->parameters['area'] = $ar->loadBySlug($app['currentSite'], $_GET['area_id']);
			if ($this->parameters['area']) {

				$checkArea = $this->parameters['area']->getParentAreaId() ? $ar->loadById($this->parameters['area']->getParentAreaId())  : null;
				while($checkArea) {
					array_unshift($this->parameters['parentAreas'],$checkArea);
					$checkArea = $checkArea->getParentAreaId() ? $ar->loadById($checkArea->getParentAreaId())  : null;
				}

				$cr = new CountryRepository();
				$this->parameters['country'] = $cr->loadById($this->parameters['area']->getCountryID());
				$venue->setCountryId($this->parameters['country']->getId());

				$areaRepoBuilder = new AreaRepositoryBuilder();
				$areaRepoBuilder->setSite($app['currentSite']);
				$areaRepoBuilder->setCountry($this->parameters['country']);
				$areaRepoBuilder->setParentArea($this->parameters['area']);
				$this->parameters['childAreas'] = $areaRepoBuilder->fetchAll();
				
				$this->parameters['startAreaBrowserFromScratch'] = false;
			}
			
		}
		
		
		
		$form = $app['form.factory']->create(new VenueNewForm($app['currentSite']), $venue);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				if (isset($_POST['areas']) && is_array($_POST['areas'])) {
					
					$area = null;
					foreach ($_POST['areas'] as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						} else if (substr($areaCode, 0, 4) == 'NEW:') {
							$newArea = new AreaModel();
							$newArea->setTitle(substr($areaCode, 4));
							$areaRepository->create($newArea, $area, $app['currentSite'], $countryRepository->loadById($venue->getCountryId()) , userGetCurrent());
							$areaRepository->buildCacheAreaHasParent($newArea);
							$area = $newArea;
						}
					}
					if ($area) {
						$venue->setAreaId($area->getId());
					}
				}
				
				$venueRepository = new VenueRepository();
				$venueRepository->create($venue, $app['currentSite'], userGetCurrent());
				
				return $app->redirect("/venue/".$venue->getSlug());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/venuenew/new.html.twig', $this->parameters);		
	}
	
	
	
	function newVenueJSON(Request $request, Application $app) {
		global $WEBSESSION;
		
		$venue = new VenueModel();
		
		$data = array();
		if ('POST' == $request->getMethod()) {
			if ($_POST['CSFRToken'] == $WEBSESSION->getCSFRToken()) {
			
				$venue->setTitle($_POST['title']);
				$venue->setDescription($_POST['description']);
				if (isset($_POST['country']) && intval($_POST['country'])) {
					$venue->setCountryId(intval($_POST['country']));
				}
				
				$venueRepository = new VenueRepository();
				$venueRepository->create($venue, $app['currentSite'], userGetCurrent());

				$data['venue'] = array(
						'id'=>$venue->getId(), 
						'slug'=>$venue->getSlug(),
						'title'=>$venue->getTitle()
					);
			}
			
		}
		
		$response = new Response(json_encode($data));
		$response->headers->set('Content-Type', 'application/json');
		return $response;

	}
	
}


