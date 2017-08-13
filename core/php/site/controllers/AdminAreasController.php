<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use repositories\CountryRepository;
use repositories\AreaRepository;
use repositories\CountryInSiteRepository;
use repositories\builders\AreaRepositoryBuilder;
use models\AreaModel;
use models\SiteModel;
use site\forms\AreaNewInAreaForm;
use site\forms\AreaNewInCountryForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminAreasController {
	
	

	
	protected $parameters = array();
	
	protected function build($countryslug, Request $request, Application $app) {
		$this->parameters = array('country'=>null,'parentAreas'=>array());
		
		$cr = new CountryRepository($app);
		// we accept both ID and Slug. Slug is proper one to use, but some JS may need to load by ID.
		$this->parameters['country'] = intval($countryslug) ? $cr->loadById($countryslug) :  $cr->loadByTwoCharCode($countryslug);
		if (!$this->parameters['country']) {
			return false;
		}
		
		// check this country is or was valid for this site
		$countryInSiteRepo = new CountryInSiteRepository($app);
		if (!$countryInSiteRepo->isCountryInSite($this->parameters['country'], $app['currentSite'])) {
			return false;
		}
		
		return true;
	}
	
	protected function buildTree(Application $app, SiteModel $site, AreaModel $parentArea = null) {
		$data = array(
				'area'=>$parentArea,
				'children'=>array()
			);
		
		$areaRepoBuilder = new AreaRepositoryBuilder($app);
		$areaRepoBuilder->setSite($site);
		$areaRepoBuilder->setCountry($this->parameters['country']);
		if ($parentArea) {
			$areaRepoBuilder->setParentArea($parentArea);
		} else {
			$areaRepoBuilder->setNoParentArea(true);
		}
		
		foreach($areaRepoBuilder->fetchAll() as $area) {
			$data['children'][] = $this->buildTree($app, $site, $area);
		}
		
		return $data;
	}
	
	
	function index($countryslug, Request $request, Application $app) {
		
		if (!$this->build($countryslug, $request, $app)) {
			$app->abort(404, "country does not exist.");
		}	
	
		
		
		$this->parameters['areaTree'] = $this->buildTree($app, $app['currentSite']);
		
		return $app['twig']->render('site/adminareas/index.html.twig', $this->parameters);
		
	}
	
	
	function action($countryslug, Request $request, Application $app) {		
		if (!$this->build($countryslug, $request, $app)) {
			$app->abort(404, "country does not exist.");
		}	
	
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$areaSlugs = is_array($request->request->get('area')) ? $request->request->get('area') : array();
			$areaRepository = new AreaRepository($app);
			if ($request->request->get('action') == 'delete') {
				foreach($areaSlugs as $areaSlug) {
					$area = $areaRepository->loadBySlugAndCountry($app['currentSite'], $areaSlug, $this->parameters['country']);
					if ($area && !$area->getIsDeleted()) {
						$areaRepository->delete($area, $app['currentUser']);
					}
					$app['flashmessages']->addMessage("Deleted!");
				}
			} else if ($request->request->get('action') == 'undelete') {
				foreach($areaSlugs as $areaSlug) {
					$area = $areaRepository->loadBySlugAndCountry($app['currentSite'], $areaSlug, $this->parameters['country']);
					if ($area) {
						$areaRepository->edit($area, $app['currentUser']);
					}
					$app['flashmessages']->addMessage("Undeleted!");
				}
			}
			
		}		
		
		
		
		return $app->redirect("/admin/areas/".$this->parameters['country']->getTwoCharCode());
		
	}
	
	function newArea($countryslug, Request $request, Application $app) {
		
		if (!$this->build($countryslug, $request, $app)) {
			$app->abort(404, "country does not exist.");
		}	
	
		$areaRepository = new AreaRepository($app);
		
		$parentArea = null; $parentAreas = array();
		if (isset($_GET['parentAreaSlug'])) {
			$parentArea = $areaRepository->loadBySlugAndCountry($app['currentSite'], $_GET['parentAreaSlug'], $this->parameters['country']);
			// build parent tree, including this area
			$checkArea = $parentArea;
			while($checkArea) {
				array_unshift($parentAreas,$checkArea);
				$checkArea = $checkArea->getParentAreaId() ? $areaRepository->loadById($checkArea->getParentAreaId())  : null;
			}
		}
		
		$area = new AreaModel();
		
		$form = $parentArea ? 
				$app['form.factory']->create( AreaNewInAreaForm::class, $area) :
				$app['form.factory']->create( AreaNewInCountryForm::class, $area);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$areaRepository->create($area, $parentArea, $app['currentSite'], $this->parameters['country'], $app['currentUser']);
				$areaRepository->buildCacheAreaHasParent($area);
				return $app->redirect("/admin/areas/".$this->parameters['country']->getTwoCharCode());
			}
		}
		
		$this->parameters['parentAreas'] = $parentAreas;
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/adminareas/newarea.html.twig', $this->parameters);
		
	}
	
}

