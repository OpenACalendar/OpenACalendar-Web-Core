<?php

namespace site\controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\ImportURLModel;
use repositories\ImportURLRepository;
use site\forms\ImportURLEditForm;
use repositories\builders\ImportURLResultRepositoryBuilder;
use repositories\builders\EventRepositoryBuilder;
use repositories\EventRepository;
use repositories\GroupRepository;
use repositories\CountryRepository;
use repositories\AreaRepository;
use repositories\builders\AreaRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportURLController {

	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array('country'=>null,'area'=>null,'parentAreas'=>array());

		$iurlRepository = new ImportURLRepository();
		$this->parameters['importurl'] =  $iurlRepository->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['importurl']) {
			return false;
		}
		
		if ($this->parameters['importurl']->getCountryID()) {
			$cr = new CountryRepository();
			$this->parameters['country'] = $cr->loadById($this->parameters['importurl']->getCountryID());
		}
		
		
		if ($this->parameters['importurl']->getGroupId()) {
			$gr = new GroupRepository();
			$this->parameters['group'] = $gr->loadById($this->parameters['importurl']->getGroupId());
		}
		
		
		if ($this->parameters['importurl']->getAreaId()) {	
			$ar = new AreaRepository();
			$this->parameters['area'] = $ar->loadById($this->parameters['importurl']->getAreaId());
			if (!$this->parameters['area']) {
				return false;
			}

			$checkArea = $this->parameters['area']->getParentAreaId() ? $ar->loadById($this->parameters['area']->getParentAreaId())  : null;
			while($checkArea) {
				array_unshift($this->parameters['parentAreas'],$checkArea);
				$checkArea = $checkArea->getParentAreaId() ? $ar->loadById($checkArea->getParentAreaId())  : null;
			}			
		}

		$app['currentUserActions']->set("org.openacalendar","importURLLog",true);
		$app['currentUserActions']->set("org.openacalendar","importURLEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeatureImporter());
		$app['currentUserActions']->set("org.openacalendar","importURLDisable",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeatureImporter()
			&& $this->parameters['importurl']->getIsEnabled());
		$app['currentUserActions']->set("org.openacalendar","importURLEnable",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeatureImporter()
			&& (!$this->parameters['importurl']->getIsEnabled() || $this->parameters['importurl']->getIsExpired()));



		return true;

	}

	function show($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}

		$irr = new ImportURLResultRepositoryBuilder();
		$irr->setImportURL($this->parameters['importurl']);
		$this->parameters['hasLogEntries'] = (boolean)(count($irr->fetchAll()) > 0);

		return $app['twig']->render('site/importurl/show.html.twig',$this->parameters);
	}

	function log($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		$irr = new ImportURLResultRepositoryBuilder();
		$irr->setImportURL($this->parameters['importurl']);
		$this->parameters['results'] = $irr->fetchAll();
			
		
		return $app['twig']->render('site/importurl/log.html.twig',$this->parameters);
	}

	function edit($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		$form = $app['form.factory']->create(new ImportURLEditForm($app['currentSite']), $this->parameters['importurl']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
								
				$area = null;
				$areaRepository = new AreaRepository();
				$postAreas = $request->request->get('areas');
				if (is_array($postAreas)) {
					foreach ($postAreas as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						}
					}
				}
				$this->parameters['importurl']->setAreaId($area ? $area->getId() : null);
				
				$iRepository = new ImportURLRepository();
				$iRepository->edit($this->parameters['importurl'], userGetCurrent());
				
				return $app->redirect("/importurl/".$this->parameters['importurl']->getSlug());
				
			}
		}
		
		
		
		if ($this->parameters['country']) {
			$areaRepoBuilder = new AreaRepositoryBuilder();
			$areaRepoBuilder->setSite($app['currentSite']);
			$areaRepoBuilder->setCountry($this->parameters['country']);
			$areaRepoBuilder->setIncludeDeleted(false);
			if ($this->parameters['area']) {
				$areaRepoBuilder->setParentArea($this->parameters['area']);
			} else {
				$areaRepoBuilder->setNoParentArea(true);
			}
			$this->parameters['childAreas'] = $areaRepoBuilder->fetchAll();
		}
		
		
		$this->parameters['form'] = $form->createView();
		
		
		return $app['twig']->render('site/importurl/edit.html.twig',$this->parameters);
	}

	function enable($slug,Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		if ($this->parameters['importurl']->getIsEnabled() && !$this->parameters['importurl']->getIsExpired()) {
			die ('NO'); // TODO
		}
		
		$iRepository = new ImportURLRepository();
		$this->parameters['clashimporturl'] = $iRepository->loadClashForImportUrl($this->parameters['importurl']);
		if ($this->parameters['clashimporturl']) {
			return $app['twig']->render('site/importurl/enable.clash.html.twig',$this->parameters);
		}
		
		if ($request->request->get('enable') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				$iRepository->enable($this->parameters['importurl'], userGetCurrent());
				return $app->redirect("/importurl/".$this->parameters['importurl']->getSlug());
		}
		
		return $app['twig']->render('site/importurl/enable.html.twig',$this->parameters);
	}

	function disable($slug,Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		if (!$this->parameters['importurl']->getIsEnabled()) {
			die ('NO'); // TODO
		}
		
		if ($request->request->get('disable') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				$iRepository = new ImportURLRepository();
				$iRepository->disable($this->parameters['importurl'], userGetCurrent());
				
				$erb = new EventRepositoryBuilder();
				$erb->setAfterNow();
				$erb->setIncludeDeleted(false);
				$erb->setImportURL($this->parameters['importurl']);
				$eventRepository = new EventRepository();
				foreach($erb->fetchAll() as $event) {
					$eventRepository->delete($event, userGetCurrent());
				}
				
				
				return $app->redirect("/importurl/".$this->parameters['importurl']->getSlug());
		}
		
		return $app['twig']->render('site/importurl/disable.html.twig',$this->parameters);
	}
}

