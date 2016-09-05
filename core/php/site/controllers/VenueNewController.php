<?php

namespace site\controllers;

use actions\GetAreaForLatLng;
use models\VenueEditMetaDataModel;
use repositories\builders\CountryRepositoryBuilder;
use repositories\CountryInSiteRepository;
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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueNewController {
	
	protected $parameters = array();
	
	function newVenue(Request $request, Application $app) {
		$areaRepository = new AreaRepository($app);
		$countryRepository = new CountryRepository($app);
					
		$venue = new VenueModel();
		
		
		$this->parameters = array('country'=>null,'parentAreas'=>array(),'area'=>null,'childAreas'=>array(),'startAreaBrowserFromScratch'=> true);
		
		
		if (isset($_GET['area_id'])) {
			$this->parameters['area'] = $areaRepository->loadBySlug($app['currentSite'], $_GET['area_id']);
			if ($this->parameters['area']) {

				$checkArea = $this->parameters['area']->getParentAreaId() ? $areaRepository->loadById($this->parameters['area']->getParentAreaId())  : null;
				while($checkArea) {
					array_unshift($this->parameters['parentAreas'],$checkArea);
					$checkArea = $checkArea->getParentAreaId() ? $areaRepository->loadById($checkArea->getParentAreaId())  : null;
				}

				$cr = new CountryRepository($app);
				$this->parameters['country'] = $cr->loadById($this->parameters['area']->getCountryID());
				$venue->setCountryId($this->parameters['country']->getId());

				$areaRepoBuilder = new AreaRepositoryBuilder($app);
				$areaRepoBuilder->setSite($app['currentSite']);
				$areaRepoBuilder->setCountry($this->parameters['country']);
				$areaRepoBuilder->setParentArea($this->parameters['area']);
				$areaRepoBuilder->setIncludeDeleted(false);
				$this->parameters['childAreas'] = $areaRepoBuilder->fetchAll();
				
				$this->parameters['startAreaBrowserFromScratch'] = false;
			}
			
		}
		
		
		
		$form = $app['form.factory']->create(new VenueNewForm($app, $this->getDefaultCountry($request, $app)), $venue);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$postAreas = $request->request->get('areas');
				if (is_array($postAreas)) {
					
					$area = null;
					foreach ($postAreas as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						} else if (substr($areaCode, 0, 4) == 'NEW:' && $app['currentUserPermissions']->hasPermission('org.openacalendar','AREAS_CHANGE')) {
							$newArea = new AreaModel();
							$newArea->setTitle(substr($areaCode, 4));
							$areaRepository->create($newArea, $area, $app['currentSite'], $countryRepository->loadById($venue->getCountryId()) , $app['currentUser']);
							$areaRepository->buildCacheAreaHasParent($newArea);
							$area = $newArea;
						}
					}
					if ($area) {
						$venue->setAreaId($area->getId());
					}
				}

				foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
					$extension->addDetailsToVenue($venue);
				}

                if (!$venue->getAreaId() && $venue->getLat()) {
                    $getAreaFromLatLng = new GetAreaForLatLng($app, $app['currentSite']);
                    $area = $getAreaFromLatLng->getArea($venue->getLat(), $venue->getLng(), $countryRepository->loadById($venue->getCountryId()));
                    if ($area) {
                        $venue->setAreaId($area->getId());
                    }
                }

                $venueEditMetaData = new VenueEditMetaDataModel();
				$venueEditMetaData->setUserAccount($app['currentUser']);
				if ($form->has('edit_comment')) {
					$venueEditMetaData->setEditComment($form->get('edit_comment')->getData());
				}
				$venueEditMetaData->setFromRequest($request);

				$venueRepository = new VenueRepository($app);
				$venueRepository->createWithMetaData($venue, $app['currentSite'], $venueEditMetaData);
				
				return $app->redirect("/venue/".$venue->getSlug());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/venuenew/new.html.twig', $this->parameters);		
	}

    /**
     * @return \models\CountryModel
     */
    protected function getDefaultCountry(Request $request, Application $app) {

        // Option 1 - is it passed in URL?
        if ($request->query->get('country_id')) {
            $cr = new CountryRepository($app);
            $country = $cr->loadByTwoCharCode($request->query->get('country_id'));
            if ($country) {
                $cisr = new CountryInSiteRepository($app);
                if ($cisr->isCountryInSite($country, $app['currentSite'])) {
                    return $country;
                }
            }
        }

        // Option 2 - work it out from Timezone?
        $crb = new CountryRepositoryBuilder($app);
        $crb->setSiteIn($app['currentSite']);
        foreach($crb->fetchAll() as $country) {
            if (in_array($app['currentTimeZone'], $country->getTimezonesAsList())) {
                return $country;
            }
        }

        // This should never happen????
        return null;

    }
	
	function newVenueJSON(Request $request, Application $app) {		
		$venue = new VenueModel();
		
		$data = array();
		if ('POST' == $request->getMethod()) {
			if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			
				$venue->setTitle($request->request->get('title'));
				$venue->setDescription($request->request->get('description'));
				if (intval($request->request->get('country'))) {
					$venue->setCountryId(intval($request->request->get('country')));
				}
				
				$venueRepository = new VenueRepository($app);
				$venueRepository->create($venue, $app['currentSite'], $app['currentUser']);

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


