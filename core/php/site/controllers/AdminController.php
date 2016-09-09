<?php

namespace site\controllers;

use models\UserGroupEditMetaDataModel;
use models\UserGroupModel;
use repositories\builders\UserGroupRepositoryBuilder;
use repositories\SiteFeatureRepository;
use repositories\UserGroupRepository;
use repositories\UserHasNoEditorPermissionsInSiteRepository;
use repositories\UserPermissionsRepository;
use Silex\Application;
use site\forms\AdminUserGroupNewForm;
use site\forms\SiteEditProfileForm;
use site\forms\AdminTagNewForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\MediaModel;
use models\TagModel;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use repositories\CountryInSiteRepository;
use repositories\MediaRepository;
use repositories\SiteProfileMediaRepository;
use repositories\TagRepository;
use repositories\builders\UserAccountRepositoryBuilder;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;
use site\forms\AdminVisibilityPublicForm;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class AdminController {
	
	function index(Application $app) {
		return $app['twig']->render('site/admin/index.html.twig', array(
			));
	}

	function listUserGroups(Application $app) {

		$ugrb = new UserGroupRepositoryBuilder($app);
		$ugrb->setSite($app['currentSite']);

		return $app['twig']->render('site/admin/listUserGroups.html.twig', array(
				'usergroups'=>$ugrb->fetchAll(),
			));

	}

	function listUsers(Application $app) {


		$upr = new UserPermissionsRepository($app);



		return $app['twig']->render('site/admin/listUsers.html.twig', array(
				'userPermissionForAnonymous'=>$upr->getPermissionsForAnonymousInSite($app['currentSite'], false, true)->getPermissions(),
				'userPermissionForAnyUser'=>$upr->getPermissionsForAnyUserInSite($app['currentSite'], false, true)->getPermissions(),
				'userPermissionForAnyVerifiedUser'=>$upr->getPermissionsForAnyVerifiedUserInSite($app['currentSite'], false, true)->getPermissions(),
			));

	}

	function listUsersNotEditors(Application $app, Request $request) {



		$repo = new UserHasNoEditorPermissionsInSiteRepository($app);


		if ($request->request->get('action') == "add" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$ur = new UserAccountRepository($app);
			$user = $ur->loadByUserName($request->request->get('username'));
			if ($user) {
				$repo->addUserToSite($user, $app['currentSite'], $app['currentUser']);
				return $app->redirect('/admin/usernoteditor/');
			}
		} else if ($request->request->get('action') == "remove" && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$ur = new UserAccountRepository($app);
			$user = $ur->loadByID($request->request->get('id'));
			if ($user) {
				$repo->removeUserFromSite($user, $app['currentSite'], $app['currentUser']);
				return $app->redirect('/admin/usernoteditor/');
			}
		}


		$userAccountRepoBuilder = new UserAccountRepositoryBuilder($app);
		$userAccountRepoBuilder->setUserHasNoEditorPermissionsInSite($app['currentSite']);

		return $app['twig']->render('site/admin/listUsersNotEditors.html.twig', array(
			'users'=>$userAccountRepoBuilder->fetchAll(),
		));
	}


	function newUserGroup(Application $app, Request $request) {

		$userGroup = new UserGroupModel();

		$form = $app['form.factory']->create(new AdminUserGroupNewForm($app['config']), $userGroup);

		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

                $userGroupEditMetaData = new UserGroupEditMetaDataModel();
                $userGroupEditMetaData->setFromRequest($request);
                $userGroupEditMetaData->setUserAccount($app['currentUser']);

				$ugRepository = new UserGroupRepository($app);
				$ugRepository->createForSiteWithMetaData($app['currentSite'], $userGroup, $userGroupEditMetaData);
				return $app->redirect("/admin/usergroup/".$userGroup->getId());

			}
		}


		return $app['twig']->render('site/admin/newUserGroup.html.twig', array(
			'form'=>$form->createView(),
		));

	}

		
	function profile(Request $request, Application $app) {		
		$form = $app['form.factory']->create(new SiteEditProfileForm($app['config']), $app['currentSite']);

        $siteLogoAllowed = $app['config']->isFileStore() && !$app['config']->isSingleSiteMode;

		if ('POST' == $request->getMethod()) {
			$form->bind($request);
			
			if ($form->isValid()) {
				
				$siteRepository = new SiteRepository($app);
				$siteRepository->edit($app['currentSite'], $app['currentUser']);

				if ($siteLogoAllowed) {
					$newLogo = $form['logo']->getData();
					if ($newLogo) {
						$mediaRepository = new MediaRepository($app);
						$media = $mediaRepository->createFromFile($newLogo, $app['currentSite'], $app['currentUser']);
						if ($media) {
							$app['currentSite']->setLogoMediaId($media->getId());
							$siteProfileMediaRepository = new SiteProfileMediaRepository($app);
							$siteProfileMediaRepository->createOrEdit($app['currentSite'], $app['currentUser']);
						}
					}
				}
				
				$app['flashmessages']->addMessage("Details saved.");
				return $app->redirect("/admin/");
				
			}
		}
		
		
		return $app['twig']->render('site/admin/profile.html.twig', array(
				'form'=>$form->createView(),
                'siteLogoAllowed' => $siteLogoAllowed
			));
	}
		
	function features(Request $request, Application $app) {

		$siteFeatureRepository = new SiteFeatureRepository($app);

		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken() && $request->request->get('action') == 'on') {
			$ext = $app['extensions']->getExtensionById($request->request->get('extension'));
			if ($ext) {
				foreach($ext->getSiteFeatures($app['currentSite']) as $feature) {
					if ($feature->getFeatureId() == $request->request->get('feature')) {
						$siteFeatureRepository->setFeature($app['currentSite'], $feature, true, $app['currentUser']);
						$app['flashmessages']->addMessage("Feature turned on.");
						return $app->redirect("/admin/features");
					}
				}
			}
		} else if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken() && $request->request->get('action') == 'off') {
			$ext = $app['extensions']->getExtensionById($request->request->get('extension'));
			if ($ext) {
				foreach($ext->getSiteFeatures($app['currentSite']) as $feature) {
					if ($feature->getFeatureId() == $request->request->get('feature')) {
						$siteFeatureRepository->setFeature($app['currentSite'], $feature, false, $app['currentUser']);
						$app['flashmessages']->addMessage("Feature turned off.");
						return $app->redirect("/admin/features");
					}
				}
			}
		}

		return $app['twig']->render('site/admin/features.html.twig', array(
		));

	}
		
	function settings(Request $request, Application $app) {		
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				
			$app['currentSite']->setPromptEmailsDaysInAdvance($request->request->get('PromptEmailsDaysInAdvance'));

			$siteRepository = new SiteRepository($app);
			$siteRepository->edit($app['currentSite'], $app['currentUser']);

			$app['flashmessages']->addMessage("Details saved.");
			return $app->redirect("/admin/");
			
		}
		
		return $app['twig']->render('site/admin/settings.html.twig', array(
			));
	}
	
	
	function visibility(Request $request, Application $app) {
		$form = $app['form.factory']->create(new AdminVisibilityPublicForm($app['config']), $app['currentSite']);
				
		if ('POST' == $request->getMethod()) {
			$form->bind($request);
				
			if ($form->isValid()) {
				
				$siteRepository = new SiteRepository($app);
				$siteRepository->edit($app['currentSite'], $app['currentUser']);
				
				return $app->redirect("/admin/");
				
			}
		}
		
		return $app['twig']->render('site/admin/visibilityPublic.html.twig', array(
				'form' => $form->createView(),
			));
	}

	
	
	function countries(Request $request, Application $app) {		
		
		$crb = new CountryRepositoryBuilder($app);
		$crb->setSiteInformation($app['currentSite']);
		$countries = $crb->fetchAll();
		
		if ($request->request->get('submitted') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$in = is_array($request->request->get('country')) ? $request->request->get('country') : null;
			$cisr = new CountryInSiteRepository($app);
			$countriesCount = 0;
			$timezones = array();
			foreach($countries as $country) {
				if (isset($in[$country->getTwoCharCode()]) && $in[$country->getTwoCharCode()] == 'yes') {
					$cisr->addCountryToSite($country, $app['currentSite'], $app['currentUser']);
					$countriesCount++;
					foreach(explode(",", $country->getTimezones()) as $timeZone) {
						$timezones[] = $timeZone;
					}
				} else {
					$cisr->removeCountryFromSite($country, $app['currentSite'], $app['currentUser']);
				}
			}
			
			$app['currentSite']->setCachedTimezonesAsList($timezones);
			$app['currentSite']->setCachedIsMultipleCountries($countriesCount > 1);
			
			$siteRepository = new SiteRepository($app);
			$siteRepository->editCached($app['currentSite']);

			return $app->redirect('/admin/');
		}
			
		return $app['twig']->render('site/admin/countries.html.twig', array(
				'countries'=>$countries,
			));
	}
	
	
	function media(Request $request, Application $app) {
		
		$mrb = new MediaRepositoryBuilder($app);
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
		$crb = new CountryRepositoryBuilder($app);
		$crb->setSiteIn($app['currentSite']);
		$countries = $crb->fetchAll();
		
		return $app['twig']->render('site/admin/areas.html.twig', array(
				'countries'=>$countries,
			));
	}


		
	
}


