<?php

namespace sysadmin\controllers;

use repositories\SiteFeatureRepository;
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
use sysadmin\forms\ActionWithCommentForm;
use sysadmin\ActionParser;
use repositories\builders\SysadminCommentRepositoryBuilder;
use repositories\SysAdminCommentRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteController {
	
		
	protected $parameters = array();
	
	protected function build($id, Request $request, Application $app) {
		$this->parameters = array('group'=>null);

		$sr = new SiteRepository($app);
		$this->parameters['site'] = $sr->loadById($id);
		
		if (!$this->parameters['site']) {
			$app->abort(404);
		}
	
	}
	
	function show($id, Request $request, Application $app) {

		$this->build($id, $request, $app);
		
				
		$siteQuotaRepository = new SiteQuotaRepository($app);
		$userRepository = new UserAccountRepository($app);
		
		$form = $app['form.factory']->create( ActionWithCommentForm::class);
		
		if ('POST' == $request->getMethod()) {
			$form->handleRequest($request);
			
			
			if ($form->isValid()) {
				$data = $form->getData();
				$action = new ActionParser($data['action']);

				$sr = new SiteRepository($app);


				$redirect = false;

				if ($data['comment']) {
					$scr = new SysAdminCommentRepository($app);
					$scr->createAboutSite($this->parameters['site'], $data['comment'], $app['currentUser']);
					$redirect = true;
				}


				if ($action->getCommand() == 'close') {
					$this->parameters['site']->setIsClosedBySysAdmin(true);
					$this->parameters['site']->setClosedBySysAdminreason($action->getParam(0));
					$sr->edit($this->parameters['site'], $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'open') {
					$this->parameters['site']->setIsClosedBySysAdmin(false);
					$this->parameters['site']->setClosedBySysAdminreason(null);
					$sr->edit($this->parameters['site'], $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					
				} else if ($action->getCommand() == 'webrobots') {
					$this->parameters['site']->setIsWebRobotsAllowed($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());

				} else if ($action->getCommand() == 'listedinindex') {
					$this->parameters['site']->setIsListedInIndex($action->getParamBoolean(0));
					$sr->edit($this->parameters['site'], $app['currentUser']);
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());

				} else if ($action->getCommand() == 'quota') {
					$sitequota = $siteQuotaRepository->loadByCode($action->getParam(0));
					if ($sitequota) {
						$this->parameters['site']->setSiteQuotaId($sitequota->getId());
						$sr->editQuota($this->parameters['site'], $app['currentUser']);
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					}
					
					
				} else if ($action->getCommand() == 'newslug') {
					$newslug = $action->getParam(0);
					if (ctype_alnum($newslug) && strlen($newslug) > 1) {
						$this->parameters['site']->setSlug($newslug);
						$sr->editSlug($this->parameters['site'], $app['currentUser']);
						return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
					}
					
				}

				if ($redirect) {
					return $app->redirect('/sysadmin/site/'.$this->parameters['site']->getId());
				}
		
			}
			
		}
		
		$this->parameters['form'] = $form->createView();
		
		
		$this->parameters['sitequota'] = $this->parameters['site']->getSiteQuotaId() ?
				$siteQuotaRepository->loadById($this->parameters['site']->getSiteQuotaId()) : 
				null;

		$sacrb = new SysadminCommentRepositoryBuilder($app);
		$sacrb->setSite($this->parameters['site']);
		$this->parameters['comments'] = $sacrb->fetchAll();

		return $app['twig']->render('sysadmin/site/show.html.twig', $this->parameters);		
	
	}
	
	function watchers($id, Request $request, Application $app) {
		$this->build($id, $request, $app);
			
		$uarb = new UserAccountRepositoryBuilder($app);
		$uarb->setWatchesSite($this->parameters['site']);
		$this->parameters['watchers'] = $uarb->fetchAll();

		return $app['twig']->render('sysadmin/site/watchers.html.twig', $this->parameters);		
	}

    function features($id, Request $request, Application $app) {
        $this->build($id, $request, $app);

        $siteFeatureRepository = new SiteFeatureRepository($app);

        if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken() && $request->request->get('action') == 'on') {
            $ext = $app['extensions']->getExtensionById($request->request->get('extension'));
            if ($ext) {
                foreach($ext->getSiteFeatures($this->parameters['site']) as $feature) {
                    if ($feature->getFeatureId() == $request->request->get('feature')) {
                        $siteFeatureRepository->setFeature($this->parameters['site'], $feature, true, $app['currentUser']);
                        return $app->redirect("/sysadmin/site/".$this->parameters['site']->getid().'/features');
                    }
                }
            }
        } else if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken() && $request->request->get('action') == 'off') {
            $ext = $app['extensions']->getExtensionById($request->request->get('extension'));
            if ($ext) {
                foreach($ext->getSiteFeatures($this->parameters['site']) as $feature) {
                    if ($feature->getFeatureId() == $request->request->get('feature')) {
                        $siteFeatureRepository->setFeature($this->parameters['site'], $feature, false, $app['currentUser']);
                        return $app->redirect("/sysadmin/site/".$this->parameters['site']->getid().'/features');
                    }
                }
            }
        }

        $this->parameters['siteFeatures'] = $siteFeatureRepository->getForSiteAsList($this->parameters['site']);

        return $app['twig']->render('sysadmin/site/features.html.twig', $this->parameters);
    }

	function listCountries($siteid, Request $request, Application $app) {
		$this->build($siteid, $request, $app);
		
		$crb = new CountryRepositoryBuilder($app);
		$crb->setSiteIn($this->parameters['site']);
		$this->parameters['countries'] = $crb->fetchAll();
		
		return $app['twig']->render('sysadmin/site/countries.html.twig', $this->parameters);		

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
	
	function showCountry($siteid, $countrycode, Request $request, Application $app) {
		$this->build($siteid, $request, $app);
		
		$cr = new CountryRepository($app);
		$this->parameters['country'] = $cr->loadByTwoCharCode($countrycode);
		if (!$this->parameters['country']) {
			die("No Country");
		}
		
		$this->parameters['areaTree'] = $this->buildTree($app, $this->parameters['site']);
		
		return $app['twig']->render('sysadmin/site/country.html.twig', $this->parameters);	
	}

	function listUsersNotEditors($siteid, Request $request, Application $app) {
		$this->build($siteid, $request, $app);

		$userAccountRepoBuilder = new UserAccountRepositoryBuilder($app);
		$userAccountRepoBuilder->setUserHasNoEditorPermissionsInSite($this->parameters['site']);
		$this->parameters['users'] = $userAccountRepoBuilder->fetchAll();

		$this->parameters['featureActive'] = $app['config']->isSingleSiteMode ? false : true;

		return $app['twig']->render('sysadmin/site/usersnoteditors.html.twig', $this->parameters);

	}

}
