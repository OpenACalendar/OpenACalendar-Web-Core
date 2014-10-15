<?php

namespace site\controllers;

use models\UserGroupModel;
use repositories\builders\UserGroupRepositoryBuilder;
use repositories\UserGroupRepository;
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

	function listUserGroups(Application $app) {

		$ugrb = new UserGroupRepositoryBuilder();
		$ugrb->setSite($app['currentSite']);

		return $app['twig']->render('site/admin/listUserGroups.html.twig', array(
				'usergroups'=>$ugrb->fetchAll(),
			));

	}

	function listUsers(Application $app) {


		$upr = new UserPermissionsRepository($app['extensions']);



		return $app['twig']->render('site/admin/listUsers.html.twig', array(
				'userPermissionForAnonymous'=>$upr->getPermissionsForAnonymousInSite($app['currentSite'], false, false)->getPermissions(),
				'userPermissionForAnyUser'=>$upr->getPermissionsForAnyUserInSite($app['currentSite'], false, false)->getPermissions(),
				'userPermissionForAnyVerifiedUser'=>$upr->getPermissionsForAnyVerifiedUserInSite($app['currentSite'], false, false)->getPermissions(),
			));

	}

	function newUserGroup(Application $app, Request $request) {

		$userGroup = new UserGroupModel();

		$form = $app['form.factory']->create(new AdminUserGroupNewForm($app['config']), $userGroup);

		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

				$ugRepository = new UserGroupRepository();
				$ugRepository->createForSite($app['currentSite'], $userGroup, userGetCurrent());
				return $app->redirect("/admin/usergroup/".$userGroup->getId());

			}
		}


		return $app['twig']->render('site/admin/newUserGroup.html.twig', array(
			'form'=>$form->createView(),
		));

	}

		
	function profile(Request $request, Application $app) {		
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
				
				$app['flashmessages']->addMessage("Details saved.");
				return $app->redirect("/admin/");
				
			}
		}
		
		
		return $app['twig']->render('site/admin/profile.html.twig', array(
				'form'=>$form->createView(),
			));
	}
		
	function features(Request $request, Application $app) {		
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				
			$app['currentSite']->setIsFeatureGroup($request->request->get('isFeatureGroup') == '1');
			$app['currentSite']->setIsFeatureMap($request->request->get('isFeatureMap') == '1');
			$app['currentSite']->setIsFeatureCuratedList($request->request->get('isFeatureCuratedList')== '1');
			$app['currentSite']->setisFeatureVirtualEvents($request->request->get('isFeatureVirtualEvents') == '1');
			$app['currentSite']->setisFeaturePhysicalEvents($request->request->get('isFeaturePhysicalEvents') == '1');
			$app['currentSite']->setIsFeatureImporter($request->request->get('isFeatureImporter') == '1');
			$app['currentSite']->setIsFeatureTag($request->request->get('isFeatureTag') == '1');

			$siteRepository = new SiteRepository();
			$siteRepository->edit($app['currentSite'], userGetCurrent());

			$app['flashmessages']->addMessage("Details saved.");
			return $app->redirect("/admin/");
			
		}
		
		return $app['twig']->render('site/admin/features.html.twig', array(
			));
	}
		
	function settings(Request $request, Application $app) {		
		if ('POST' == $request->getMethod() && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
				
			$app['currentSite']->setPromptEmailsDaysInAdvance($request->request->get('PromptEmailsDaysInAdvance'));

			$siteRepository = new SiteRepository();
			$siteRepository->edit($app['currentSite'], userGetCurrent());

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
				
				$siteRepository = new SiteRepository();
				$siteRepository->edit($app['currentSite'], userGetCurrent());
				
				return $app->redirect("/admin/");
				
			}
		}
		
		return $app['twig']->render('site/admin/visibilityPublic.html.twig', array(
				'form' => $form->createView(),
			));
	}

	
	
	function countries(Request $request, Application $app) {		
		
		$crb = new CountryRepositoryBuilder();
		$crb->setSiteInformation($app['currentSite']);
		$countries = $crb->fetchAll();
		
		if ($request->request->get('submitted') == 'yes' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
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


