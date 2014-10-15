<?php

namespace sysadmin\controllers;

use Silex\Application;
use site\forms\NewEventForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\EventModel;
use models\AreaModel;
use repositories\SiteRepository;
use repositories\SiteQuotaRepository;
use repositories\UserAccountRepository;
use repositories\CountryRepository;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\AreaRepositoryBuilder;
use repositories\builders\UserAccountRepositoryBuilder;
use sysadmin\forms\ActionForm;
use sysadmin\ActionParser;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteController {
	
		
	protected $parameters = array();
	
	protected function build($id, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository();
		$this->parameters['site'] = $sr->loadById($id);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
	
	}
	
	function show($id, Request $request, Application $app) {

		$this->build($id, $request, $app);
		
				
		$siteQuotaRepository = new SiteQuotaRepository();
		$userRepository = new UserAccountRepository();
		
		$form = $app['form.factory']->create(new ActionForm());
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			
			
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);

				$sr = new SiteRepository();
				
				if ($action->getCommand() == 'close') {
					$this->parameters['site']->setIsClosedBySysAdmin(true);
					$this->parameters['site']->setClosedBySysAdminreason($action->getParam(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'open') {
					$this->parameters['site']->setIsClosedBySysAdmin(false);
					$this->parameters['site']->setClosedBySysAdminreason(null);
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'webrobots') {
					$this->parameters['site']->setIsWebRobotsAllowed($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'allusersedit') {
					$this->parameters['site']->setIsAllUsersEditors($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'requestaccessallowed') {
					$this->parameters['site']->setIsRequestAccessAllowed($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'featuremap') {
					$this->parameters['site']->setIsFeatureMap($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'featuregroup') {
					$this->parameters['site']->setIsFeatureGroup($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'featureimporter') {
					$this->parameters['site']->setIsFeatureImporter($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'featurecuratedlist') {
					$this->parameters['site']->setIsFeatureCuratedList($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'featurephysicalevents') {
					$this->parameters['site']->setIsFeaturePhysicalEvents($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'featurevirtualevents') {
					$this->parameters['site']->setIsFeatureVirtualEvents($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'listedinindex') {
					$this->parameters['site']->setIsListedInIndex($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], userGetCurrent());
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());

				} else if ($action->getCommand() == 'quota') {
					$sitequota = $siteQuotaRepository->loadByCode($action->getParam(0));
					if ($sitequota) {
						$this->parameters['site']->setSiteQuotaId($sitequota->getId());
						$sr->editQuota($this->parameters['site'], userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					}
					
					
				} else if ($action->getCommand() == 'newslug') {
					$newslug = $action->getParam(0);
					if (ctype_alnum($newslug) && strlen($newslug) > 1) {
						$this->parameters['site']->setSlug($newslug);
						$sr->editSlug($this->parameters['site'], userGetCurrent());
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					}
					
				}
		
			}
			
		}
		
		$this->parameters['form'] = $form->createView();
		
		
		$this->parameters['sitequota'] = $this->parameters['site']->getSiteQuotaId() ?
				$siteQuotaRepository->loadById($this->parameters['site']->getSiteQuotaId()) : 
				null;
		
		return $app['twig']->render('sysadmin/site/show.html.twig', $this->parameters);		
	
	}
	
	function watchers($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
			
		$uarb = new UserAccountRepositoryBuilder();
		$uarb->setWatchesSite($this->parameters['site']);
		$this->parameters['watchers'] = $uarb->fetchAll();

		return $app['twig']->render('sysadmin/site/watchers.html.twig', $this->parameters);		
	}
	
	function listCountries($siteid, Request $request, Application $app) {
		$this->build($siteid, $request, $app);
		
		$crb = new CountryRepositoryBuilder();
		$crb->setSiteIn($this->parameters['site']);
		$this->parameters['countries'] = $crb->fetchAll();
		
		return $app['twig']->render('sysadmin/site/countries.html.twig', $this->parameters);		

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
	
	function showCountry($siteid, $countrycode, Request $request, Application $app) {
		$this->build($siteid, $request, $app);
		
		$cr = new CountryRepository();
		$this->parameters['country'] = $cr->loadByTwoCharCode($countrycode);
		if (!$this->parameters['country']) {
			die("No Country");
		}
		
		$this->parameters['areaTree'] = $this->buildTree($this->parameters['site']);
		
		return $app['twig']->render('sysadmin/site/country.html.twig', $this->parameters);	
	}
	
}


