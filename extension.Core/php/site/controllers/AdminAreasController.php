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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AdminAreasController {
	
	

	
	protected $parameters = array();
	
	protected function build($countryslug, Request $request, Application $app) {
		$this->parameters = array('country'=>null,'parentAreas'=>array());
		
		$cr = new CountryRepository();
		// we accept both ID and Slug. Slug is proper one to use, but some JS may need to load by ID.
		$this->parameters['country'] = intval($countryslug) ? $cr->loadById($countryslug) :  $cr->loadByTwoCharCode($countryslug);
		if (!$this->parameters['country']) {
			return false;
		}
		
		// check this country is or was valid for this site
		$countryInSiteRepo = new CountryInSiteRepository();
		if (!$countryInSiteRepo->isCountryInSite($this->parameters['country'], $app['currentSite'])) {
			return false;
		}
		
		return true;
	}
	
	protected function buildTree(SiteModel $site, AreaModel $parentArea = null) {
		$data = array(
				'area'=>$parentArea,
				'children'=>array()
			);
		
		$areaRepoBuilder = new AreaRepositoryBuilder();
		$areaRepoBuilder->setSite($site);
		$areaRepoBuilder->setCountry($this->parameters['country']);
		if ($parentArea) {
			$areaRepoBuilder->setParentArea($parentArea);
		} else {
			$areaRepoBuilder->setNoParentArea(true);
		}
		
		foreach($areaRepoBuilder->fetchAll() as $area) {
			$data['children'][] = $this->buildTree($site, $area);
		}
		
		return $data;
	}
	
	
	function index($countryslug, Request $request, Application $app) {
		
		if (!$this->build($countryslug, $request, $app)) {
			$app->abort(404, "country does not exist.");
		}	
	
		
		
		$this->parameters['areaTree'] = $this->buildTree($app['currentSite']);
		
		return $app['twig']->render('site/adminareas/index.html.twig', $this->parameters);
		
	}
	
	function newArea($countryslug, Request $request, Application $app) {
		
		if (!$this->build($countryslug, $request, $app)) {
			$app->abort(404, "country does not exist.");
		}	
	
		$areaRepository = new AreaRepository();
		
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
				$app['form.factory']->create(new AreaNewInAreaForm(), $area) :
				$app['form.factory']->create(new AreaNewInCountryForm(), $area);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$areaRepository->create($area, $parentArea, $app['currentSite'], $this->parameters['country'], userGetCurrent());
				$areaRepository->buildCacheAreaHasParent($area);
				return $app->redirect("/admin/areas/".$this->parameters['country']->getTwoCharCode());
			}
		}
		
		$this->parameters['parentAreas'] = $parentAreas;
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/adminareas/newarea.html.twig', $this->parameters);
		
	}
	
}

