<?php

namespace site\controllers;

use Silex\Application;
use site\forms\SiteEditProfileForm;
use site\forms\AdminTagNewForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\MediaModel;
use models\TagModel;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\UserInSiteRepository;
use repositories\CountryInSiteRepository;
use repositories\MediaRepository;
use repositories\SiteProfileMediaRepository;
use repositories\TagRepository;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;
use site\forms\AdminVisibilityPublicForm;
use site\forms\AdminUsersAddForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class AdminController {
	
	function index(Application $app) {
		return $app['twig']->render('site/admin/index.html.twig', array(
			));
	}
		
	
	function owner(Application $app) {
		$uar = new UserAccountRepository();
		return $app['twig']->render('site/admin/owner.html.twig', array(
				'owner'=>$uar->loadByOwnerOfSite($app['currentSite'])
			));
	}
		
	function profile(Request $request, Application $app) {
		global  $FLASHMESSAGES;
		
		$form = $app['form.factory']->create(new SiteEditProfileForm($app['config']), $app['currentSite']);
				
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			
			if ($form->isValid()) {
				
				$siteRepository = new SiteRepository();
				$siteRepository->edit($app['currentSite'], userGetCurrent());

				if ($app['config']->isFileStore()) {
					$newLogo = $form['logo']->getData();
					if ($newLogo) {
						$mediaRepository = new MediaRepository();
						$media = $mediaRepository->createFromFile($newLogo, $app['currentSite'], userGetCurrent());
						if ($media) {
							$app['currentSite']->setLogoMediaId($media->getId());
							$siteProfileMediaRepository = new SiteProfileMediaRepository();
							$siteProfileMediaRepository->createOrEdit($app['currentSite'], userGetCurrent());
						}
					}
				}
				
				$FLASHMESSAGES->addMessage("Details saved.");
				return $app->redirect("/admin/");
				
			}
		}
		
		
		return $app['twig']->render('site/admin/profile.html.twig', array(
				'form'=>$form->createView(),
			));
	}
		
	function features(Request $request, Application $app) {
		global $FLASHMESSAGES, $WEBSESSION;
		
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
				
			$app['currentSite']->setIsFeatureGroup($request->request->get('isFeatureGroup') == '1');
			$app['currentSite']->setIsFeatureMap($request->request->get('isFeatureMap') == '1');
			$app['currentSite']->setIsFeatureCuratedList($request->request->get('isFeatureCuratedList')== '1');
			$app['currentSite']->setisFeatureVirtualEvents($request->request->get('isFeatureVirtualEvents') == '1');
			$app['currentSite']->setisFeaturePhysicalEvents($request->request->get('isFeaturePhysicalEvents') == '1');
			$app['currentSite']->setIsFeatureImporter($request->request->get('isFeatureImporter') == '1');
			$app['currentSite']->setIsFeatureTag($request->request->get('isFeatureTag') == '1');

			$siteRepository = new SiteRepository();
			$siteRepository->edit($app['currentSite'], userGetCurrent());

			$FLASHMESSAGES->addMessage("Details saved.");
			return $app->redirect("/admin/");
			
		}
		
		return $app['twig']->render('site/admin/features.html.twig', array(
			));
	}
		
	function settings(Request $request, Application $app) {
		global $FLASHMESSAGES, $WEBSESSION;
		
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
				
			$app['currentSite']->setPromptEmailsDaysInAdvance($request->request->get('PromptEmailsDaysInAdvance'));

			$siteRepository = new SiteRepository();
			$siteRepository->edit($app['currentSite'], userGetCurrent());

			$FLASHMESSAGES->addMessage("Details saved.");
			return $app->redirect("/admin/");
			
		}
		
		return $app['twig']->render('site/admin/settings.html.twig', array(
			));
	}
	
	
	function visibility(Request $request, Application $app) {


		$form = $app['form.factory']->create(new AdminVisibilityPublicForm(), $app['currentSite']);
		
				
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
				
			if ($form->isValid()) {
				
				$siteRepository = new SiteRepository();
				$siteRepository->edit($app['currentSite'], userGetCurrent());
				
				return $app->redirect("/admin/");
				
			}
		}
		
		
		return $app['twig']->render('site/admin/visibilityPublic.html.twig', array(
				'form' => $form->createView(),
			));
	}
	
	function users(Request $request, Application $app) {
		global $WEBSESSION;
		
		if ($request->request->get('submitted') == 'yes' && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
			
			if ($request->request->get('isAllUsersEditors') == 'yes') {
				$app['currentSite']->setIsAllUsersEditors(true);
				// if all users can edit no need for request acces
				$app['currentSite']->setIsRequestAccessAllowed(false);
			} else {
				$app['currentSite']->setIsAllUsersEditors(false);
				if ($request->request->get('isRequestAccessAllowed') == 'yes') {
					$app['currentSite']->setIsRequestAccessAllowed(true);
					$app['currentSite']->setRequestAccessQuestion($request->request->get('requestAccessQuestion'));
				} else {
					$app['currentSite']->setIsRequestAccessAllowed(false);
				}				
			}
			
			$siteRepository = new SiteRepository();
			$siteRepository->edit($app['currentSite'], userGetCurrent());
				
			return $app->redirect("/admin/users");
		}
			
		$uarb = new UserAccountRepositoryBuilder();
		$uarb->setCanEditSite($app['currentSite']);
		$users = $uarb->fetchAll();

		$uarb = new UserAccountRepositoryBuilder();
		$uarb->setRequestAccessSite($app['currentSite']);
		$usersRequest = $uarb->fetchAll();

		return $app['twig']->render('site/admin/users.html.twig', array(
				'users'=>$users,
				'usersRequest'=>$usersRequest,
			));
		
	}
	
	function usersActions(Request $request, Application $app) {
		global $WEBSESSION;
		
		if ($request->request->get('userID')  && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
			$uisr = new UserInSiteRepository();
			$uar = new UserAccountRepository();
			if ($request->request->get('actionRemove')) {
				foreach($request->request->get('userID') as $uid) {
					$user = $uar->loadByID($uid);
					if ($user) {
						$uisr->removeUserAdministratesSite($user, $app['currentSite']);
						$uisr->removeUserEditsSite($user, $app['currentSite']);
					}
				}
			} else if ($request->request->get('actionAdministrator')) {
				foreach($request->request->get('userID') as $uid) {
					$user = $uar->loadByID($uid);
					if ($user) {
						$uisr->markUserAdministratesSite($user, $app['currentSite']);
					}
				}
			} else if ($request->request->get('actionEditor')) {
				foreach($request->request->get('userID') as $uid) {
					$user = $uar->loadByID($uid);
					if ($user) {
						$uisr->removeUserAdministratesSite($user, $app['currentSite']);
						$uisr->markUserEditsSite($user, $app['currentSite']);
					}
				}
			}
		}

		return $app->redirect('/admin/users');
	}
	
	function usersAdd(Request $request, Application $app) {
			
		$form = $app['form.factory']->create(new AdminUsersAddForm($app['currentSite']));
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$data = $form->getData();
				
				$uisr = new UserInSiteRepository();
				$uar = new UserAccountRepository();
				$user = $uar->loadByUserName($data['username']);
				if ($user) {
					if ($data['role'] == 'admin') {
						$uisr->markUserAdministratesSite($user, $app['currentSite']);
					} else {
						$uisr->markUserEditsSite($user, $app['currentSite']);
					}
					return $app->redirect("/admin/users");
				} else {
					// TODO
				}
				
				
				
			}
		}

		return $app['twig']->render('site/admin/usersAdd.html.twig', array(
				'form' => $form->createView(),
			));
		
	}
	
	
	function countries(Request $request, Application $app) {		
		global $WEBSESSION;
		
		$crb = new CountryRepositoryBuilder();
		$crb->setSiteInformation($app['currentSite']);
		$countries = $crb->fetchAll();
		
		if ($request->request->get('submitted') == 'yes' && $request->request->get('CSFRToken') == $WEBSESSION->getCSFRToken()) {
			$in = is_array($request->request->get('country')) ? $request->request->get('country') : null;
			$cisr = new CountryInSiteRepository;
			$countriesCount = 0;
			$timezones = array();
			foreach($countries as $country) {
				if (isset($in[$country->getTwoCharCode()]) && $in[$country->getTwoCharCode()] == 'yes') {
					$cisr->addCountryToSite($country, $app['currentSite'], userGetCurrent());
					$countriesCount++;
					foreach(explode(",", $country->getTimezones()) as $timeZone) {
						$timezones[] = $timeZone;
					}
				} else {
					$cisr->removeCountryFromSite($country, $app['currentSite'], userGetCurrent());
				}
			}
			
			$app['currentSite']->setCachedTimezonesAsList($timezones);
			$app['currentSite']->setCachedIsMultipleCountries($countriesCount > 1);
			
			$siteRepository = new SiteRepository();
			$siteRepository->editCached($app['currentSite']);

			return $app->redirect('/admin/');
		}
			
		return $app['twig']->render('site/admin/countries.html.twig', array(
				'countries'=>$countries,
			));
	}
	
	
	function media(Request $request, Application $app) {


		$form = $app['form.factory']->create(new AdminVisibilityPublicForm(), $app['currentSite']);
		
				
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$size = 0;
		$count = 0;
		foreach($mrb->fetchAll() as $media){
			$count += 1;
			$size += $media->getStorageSize();
		}
		
		return $app['twig']->render('site/admin/media.html.twig', array(
				'count'=>$count,
				'size'=>$size,
			));
	}
	
	function areas(Application $app) {
		$crb = new CountryRepositoryBuilder();
		$crb->setSiteIn($app['currentSite']);
		$countries = $crb->fetchAll();
		
		return $app['twig']->render('site/admin/areas.html.twig', array(
				'countries'=>$countries,
			));
	}
	
	function listTags(Application $app) {
		
		$trb = new TagRepositoryBuilder();
		$trb->setSite($app['currentSite']);
		$trb->setIncludeDeleted(true);
		$tags = $trb->fetchAll();
		
		return $app['twig']->render('site/admin/listTags.html.twig', array(	
				'tags'=>$tags,
			));
	}
	
	function newTag(Request $request, Application $app) {


		$tag = new TagModel();

		$form = $app['form.factory']->create(new AdminTagNewForm(), $tag);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

				$tagRepo = new TagRepository();
				$tagRepo->create($tag, $app['currentSite'], userGetCurrent());
				
				return $app->redirect('/admin/tag/'.$tag->getSlugForUrl());
				
			}
		}



		return $app['twig']->render('site/admin/newTag.html.twig', array(
				'form' => $form->createView(),	
			));


	}
		
	
}


