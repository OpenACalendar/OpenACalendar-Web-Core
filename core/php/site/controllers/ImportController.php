<?php

namespace site\controllers;

use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserAccountRepository;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use models\ImportModel;
use repositories\ImportRepository;
use site\forms\ImportEditForm;
use repositories\builders\ImportResultRepositoryBuilder;
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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportController {

	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		$this->parameters = array('country'=>null,'area'=>null,'parentAreas'=>array());

		$iurlRepository = new ImportRepository($app);
		$this->parameters['import'] =  $iurlRepository->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['import']) {
			return false;
		}
		
		if ($this->parameters['import']->getCountryID()) {
			$cr = new CountryRepository($app);
			$this->parameters['country'] = $cr->loadById($this->parameters['import']->getCountryID());
		}
		
		
		if ($this->parameters['import']->getGroupId()) {
			$gr = new GroupRepository($app);
			$this->parameters['group'] = $gr->loadById($this->parameters['import']->getGroupId());
		}
		
		
		if ($this->parameters['import']->getAreaId()) {	
			$ar = new AreaRepository($app);
			$this->parameters['area'] = $ar->loadById($this->parameters['import']->getAreaId());
			if (!$this->parameters['area']) {
				return false;
			}

			$checkArea = $this->parameters['area']->getParentAreaId() ? $ar->loadById($this->parameters['area']->getParentAreaId())  : null;
			while($checkArea) {
				array_unshift($this->parameters['parentAreas'],$checkArea);
				$checkArea = $checkArea->getParentAreaId() ? $ar->loadById($checkArea->getParentAreaId())  : null;
			}			
		}

		$app['currentUserActions']->set("org.openacalendar","importLog",true);
		$app['currentUserActions']->set("org.openacalendar","importEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","IMPORTURL_CHANGE")
			&& $app['currentSiteFeatures']->has('org.openacalendar','Importer'));
		$app['currentUserActions']->set("org.openacalendar","importDisable",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","IMPORTURL_CHANGE")
			&& $app['currentSiteFeatures']->has('org.openacalendar','Importer')
			&& $this->parameters['import']->getIsEnabled());
		$app['currentUserActions']->set("org.openacalendar","importEnable",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","IMPORTURL_CHANGE")
			&& $app['currentSiteFeatures']->has('org.openacalendar','Importer')
			&& (!$this->parameters['import']->getIsEnabled() || $this->parameters['import']->getIsExpired()));



		return true;

	}

	function show($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}

		$irr = new ImportResultRepositoryBuilder($app);
		$irr->setImport($this->parameters['import']);
		$this->parameters['hasLogEntries'] = (boolean)(count($irr->fetchAll()) > 0);

		$this->parameters['refreshForMoreLogEntries'] = ($this->parameters['hasLogEntries'] == 0) ? $app['messagequeproducerhelper']->hasMessageQue() : false;

		return $app['twig']->render('site/import/show.html.twig',$this->parameters);
	}

	function log($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		$irr = new ImportResultRepositoryBuilder($app);
		$irr->setImport($this->parameters['import']);
		$this->parameters['results'] = $irr->fetchAll();
			
		
		return $app['twig']->render('site/import/log.html.twig',$this->parameters);
	}

	function edit($slug,Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		$form = $app['form.factory']->create(new ImportEditForm($app, $app['currentSite']), $this->parameters['import']);
		
		if ('POST' == $request->getMethod()) {
			$form->handleRequest($request);

			if ($form->isValid()) {
								
				$area = null;
				$areaRepository = new AreaRepository($app);
				$postAreas = $request->request->get('areas');
				if (is_array($postAreas)) {
					foreach ($postAreas as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						}
					}
				}
				$this->parameters['import']->setAreaId($area ? $area->getId() : null);
				
				$iRepository = new ImportRepository($app);
				$iRepository->edit($this->parameters['import'], $app['currentUser']);
				
				return $app->redirect("/import/".$this->parameters['import']->getSlug());
				
			}
		}
		
		
		
		if ($this->parameters['country']) {
			$areaRepoBuilder = new AreaRepositoryBuilder($app);
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
		
		
		return $app['twig']->render('site/import/edit.html.twig',$this->parameters);
	}

	function enable($slug,Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		if ($this->parameters['import']->getIsEnabled() && !$this->parameters['import']->getIsExpired()) {
			die ('NO'); // TODO
		}
		
		$iRepository = new ImportRepository($app);
		$this->parameters['clashimport'] = $iRepository->loadClashForImportUrl($this->parameters['import']);
		if ($this->parameters['clashimport']) {
			return $app['twig']->render('site/import/enable.clash.html.twig',$this->parameters);
		}
		
		if ($request->request->get('enable') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				$iRepository->enable($this->parameters['import'], $app['currentUser']);
				return $app->redirect("/import/".$this->parameters['import']->getSlug());
		}
		
		return $app['twig']->render('site/import/enable.html.twig',$this->parameters);
	}

	function disable($slug,Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}
		
		if (!$this->parameters['import']->getIsEnabled()) {
			die ('NO'); // TODO
		}
		
		if ($request->request->get('disable') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				$iRepository = new ImportRepository($app);
				$iRepository->disable($this->parameters['import'], $app['currentUser']);
				
				$erb = new EventRepositoryBuilder($app);
				$erb->setAfterNow();
				$erb->setIncludeDeleted(false);
				$erb->setImport($this->parameters['import']);
				$eventRepository = new EventRepository($app);
				foreach($erb->fetchAll() as $event) {
					$eventRepository->delete($event, $app['currentUser']);
				}
				
				
				return $app->redirect("/import/".$this->parameters['import']->getSlug());
		}
		
		return $app['twig']->render('site/import/disable.html.twig',$this->parameters);
	}


	function enableFromNotification($slug, $userid, $usercode, Request $request, Application $app) {
		// Check Import ......
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Import does not exist.");
		}

		if ($this->parameters['import']->getIsEnabled() && !$this->parameters['import']->getIsExpired()) {
			return $app['twig']->render('site/import/enable.fromNotification.alreadyDone.html.twig',$this->parameters);
		}

		$iRepository = new ImportRepository($app);
		$this->parameters['clashimport'] = $iRepository->loadClashForImportUrl($this->parameters['import']);
		if ($this->parameters['clashimport']) {
			return $app['twig']->render('site/import/enable.clash.html.twig',$this->parameters);
		}

		// Check User ....
		$userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($app);
		$userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->loadByUserAccountIDAndAccessKey($userid, $usercode);
		if (!$userAccountGeneralSecurityKey) {
			$app->abort(404, "User does not exist.");
		}
		$userRepo = new UserAccountRepository($app);
		$this->parameters['user'] = $userRepo->loadByID($userAccountGeneralSecurityKey->getUserAccountId());

		// Lets go
		if ($request->request->get('enable') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$iRepository->enable($this->parameters['import'], $this->parameters['user']);
			return $app->redirect("/import/".$this->parameters['import']->getSlug());
		}

		return $app['twig']->render('site/import/enable.fromNotification.html.twig',$this->parameters);
	}

}

