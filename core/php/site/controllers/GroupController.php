<?php

namespace site\controllers;

use models\EventEditMetaDataModel;
use models\GroupEditMetaDataModel;
use Silex\Application;
use site\forms\GroupNewForm;
use site\forms\GroupEditForm;
use site\forms\EventNewForm;
use site\forms\UploadNewMediaForm;
use Symfony\Component\HttpFoundation\Request;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use models\ImportURLModel;
use models\MediaModel;
use repositories\ImportURLRepository;
use repositories\GroupRepository;
use repositories\builders\GroupRepositoryBuilder;
use repositories\EventRepository;
use repositories\UserWatchesGroupRepository;
use repositories\UserWatchesGroupStopRepository;
use repositories\UserAccountRepository;
use repositories\AreaRepository;
use repositories\MediaRepository;
use repositories\MediaInGroupRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\HistoryRepositoryBuilder;
use repositories\builders\MediaRepositoryBuilder;
use repositories\builders\ImportURLRepositoryBuilder;
use org\openacalendar\curatedlists\repositories\builders\CuratedListRepositoryBuilder;
use site\forms\ImportURLNewForm;
use JMBTechnologyLimited\ParseDateTimeRangeString\ParseDateTimeRangeString;

use repositories\builders\filterparams\EventFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		global $CONFIG;
		$this->parameters = array(
			'currentUserWatchesGroup'=>false,
			'groupIsDuplicateOf'=>null);
		
		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		
		$gr = new GroupRepository();
		$this->parameters['group'] = $gr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['group']) {
			return false;
		}
		
		if ($app['currentUser']) {
			$uwgr = new UserWatchesGroupRepository();
			$uwg = $uwgr->loadByUserAndGroup($app['currentUser'], $this->parameters['group']);
			$this->parameters['currentUserWatchesGroup'] = $uwg && $uwg->getIsWatching();
		}

		if ($this->parameters['group']->getIsDuplicateOfId()) {
			$this->parameters['groupIsDuplicateOf'] = $gr->loadByID($this->parameters['group']->getIsDuplicateOfId());
		}

		$app['currentUserActions']->set("org.openacalendar","groupHistory",true);
		$app['currentUserActions']->set("org.openacalendar","groupEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","GROUPS_CHANGE")
			&& $app['currentSite']->getIsFeatureGroup()
			&& !$this->parameters['group']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","groupEditMedia",
			$CONFIG->isFileStore()
			&& $app['currentUserPermissions']->hasPermission("org.openacalendar","GROUPS_CHANGE")
			&& $app['currentSite']->getIsFeatureGroup()
			&& !$this->parameters['group']->getIsDeleted());
		// There is curatedListGeneralEdit but we want to check details on this group to
		$app['currentUserActions']->set("org.openacalendar","groupEditCuratedLists",
			$app['currentUserActions']->has("org.openacalendar","curatedListGeneralEdit")
			&& !$this->parameters['group']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","groupNewEvent",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","EVENTS_CHANGE")
			&& $app['currentSite']->getIsFeatureGroup()
			&& !$this->parameters['group']->getIsDeleted());


		return true;
	}
	
	function show($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		$this->parameters['eventListFilterParams'] = new EventFilterParams();
		$this->parameters['eventListFilterParams']->set($_GET);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeAreaInformation(true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeVenueInformation(true);
		$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setIncludeMediasSlugs(true);
		if ($app['currentUser']) {
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
		}
		
		$this->parameters['events'] = $this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->fetchAll();
		
				
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setGroup($this->parameters['group']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		// we only want to link to these if there are any
		$importurlRepoBuilder = new ImportURLRepositoryBuilder();
		$importurlRepoBuilder->setGroup($this->parameters['group']);
		$this->parameters['importurls'] = $importurlRepoBuilder->fetchAll();
		
		$groupRepo = new GroupRepository();
		if (!$this->parameters['group']->getIsDeleted() && $app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeatureGroup() ) {
			$this->parameters['isGroupRunningOutOfFutureEvents'] = $groupRepo->isGroupRunningOutOfFutureEvents($this->parameters['group'], $app['currentSite']);
		} else {
			$this->parameters['isGroupRunningOutOfFutureEvents'] = 0;
		}

		$curatedListRepoBuilder = new CuratedListRepositoryBuilder();
		$curatedListRepoBuilder->setContainsGroup($this->parameters['group']);
		$curatedListRepoBuilder->setIncludeDeleted(false);
		$this->parameters['curatedLists'] = $curatedListRepoBuilder->fetchAll();

		return $app['twig']->render('site/group/show.html.twig', $this->parameters);
	}
	
	
	function history($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		
		
		$historyRepositoryBuilder = new HistoryRepositoryBuilder();
		$historyRepositoryBuilder->setGroup($this->parameters['group']);
		$this->parameters['historyItems'] = $historyRepositoryBuilder->fetchAll();
		
		return $app['twig']->render('site/group/history.html.twig', $this->parameters);
	}
	
	
	
	function importers($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		
		
		$importurlRepoBuilder = new ImportURLRepositoryBuilder();
		$importurlRepoBuilder->setGroup($this->parameters['group']);
		$this->parameters['importurls'] = $importurlRepoBuilder->fetchAll();
		
		return $app['twig']->render('site/group/importers.html.twig', $this->parameters);
	}



	function editSplash($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		return $app['twig']->render('site/group/edit.splash.html.twig', $this->parameters);

	}

	function editMedia($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		if ($app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE") && $app['currentSite']->getIsFeatureGroup()) {
			
			
			$form = $app['form.factory']->create(new UploadNewMediaForm($this->parameters['group']->getTitle()));
		
			
			if ('POST' == $request->getMethod()) {
				$form->bind($request);

				if ($form->isValid() && $form['media']->getData()) {

					$mediaRepository = new MediaRepository();
					$media = $mediaRepository->createFromFile($form['media']->getData(), $app['currentSite'], $app['currentUser'],
							$form['title']->getData(),$form['source_text']->getData(),$form['source_url']->getData());
					
					if ($media) {

						$mediaInGroupRepo = new MediaInGroupRepository();
						$mediaInGroupRepo->add($media, $this->parameters['group'], $app['currentUser']);
						
						$app['flashmessages']->addMessage('Picture added!');
						return $app->redirect("/group/".$this->parameters['group']->getSlugForURL());
						
					}
					
				}
			}
			$this->parameters['uploadNewMediaForm'] = $form->createView();
			
		}
		
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setGroup($this->parameters['group']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		return $app['twig']->render('site/group/edit.media.html.twig', $this->parameters);
	}
	
	function editMediaRemove($slug, $mediaslug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $mediaslug);
			if ($media) {
				$mediaInGroupRepo = new MediaInGroupRepository();
				$mediaInGroupRepo->remove($media, $this->parameters['group'], $app['currentUser']);
				$app['flashmessages']->addMessage('Removed!');
			}
		}
		
		return $app->redirect("/group/".$this->parameters['group']->getSlugForURL().'/edit/media');
	}
	
	function editMediaAddExisting($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
			
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $request->request->get('addMedia'));
			if ($media) {
				$mediaInGroupRepo = new MediaInGroupRepository();
				$mediaInGroupRepo->add($media, $this->parameters['group'], $app['currentUser']);
				$app['flashmessages']->addMessage('Added!');
				return $app->redirect("/group/".$this->parameters['group']->getSlugForURL().'/');
			}
		}
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setNotInGroup($this->parameters['group']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		return $app['twig']->render('site/group/edit.media.add.existing.html.twig', $this->parameters);
	}
	
	
	function editDetails($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		if ($this->parameters['group']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		
		$form = $app['form.factory']->create(new GroupEditForm($app), $this->parameters['group']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {

				$groupEditMetaDataModel = new GroupEditMetaDataModel();
				$groupEditMetaDataModel->setUserAccount($app['currentUser']);
				if ($form->has('edit_comment')) {
					$groupEditMetaDataModel->setEditComment($form->get('edit_comment')->getData());
				}
				$groupEditMetaDataModel->setFromRequest($request);

				$groupRepository = new GroupRepository();
				$groupRepository->editWithMetaData($this->parameters['group'], $groupEditMetaDataModel);
				
				return $app->redirect("/group/".$this->parameters['group']->getSlugForUrl());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/group/edit.details.html.twig', $this->parameters);
		
	}	

	function calendarNow($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
				
		$this->parameters['calendar'] = new \RenderCalendar();
		$this->parameters['calendar']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if ($app['currentUser']) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
			$this->parameters['showCurrentUserOptions'] = true;
		}
		$this->parameters['calendar']->byDate(\TimeSource::getDateTime(), 31, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['group']->getTitle();
		return $app['twig']->render('/site/calendarPage.html.twig', $this->parameters);
	}
	
	function calendar($slug, $year, $month, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		
		$this->parameters['calendar'] = new \RenderCalendar();
		$this->parameters['calendar']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if ($app['currentUser']) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount($app['currentUser'], true);
			$this->parameters['showCurrentUserOptions'] = true;
		}
		$this->parameters['calendar']->byMonth($year, $month, true);
		
		list($this->parameters['prevYear'],$this->parameters['prevMonth'],$this->parameters['nextYear'],$this->parameters['nextMonth']) = $this->parameters['calendar']->getPrevNextLinksByMonth();
		
		$this->parameters['pageTitle'] = $this->parameters['group']->getTitle();
		return $app['twig']->render('/site/calendarPage.html.twig', $this->parameters);
	}
	
	
	function watch($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		if ($request->request->get('action')  && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$repo = new UserWatchesGroupRepository();
			if ($request->request->get('action') == 'watch') {
				$repo->startUserWatchingGroup($app['currentUser'], $this->parameters['group']);
				$app['flashmessages']->addMessage("Watching!");
			} else if ($request->request->get('action') == 'unwatch') {
				$repo->stopUserWatchingGroup($app['currentUser'], $this->parameters['group']);
				$app['flashmessages']->addMessage("No longer watching");
			}
			// redirect here because if we didn't the  $this->parameters vars would be wrong (the old state)
			// this is an easy way to get round that. Also it's nice UI to go back to the group page.
			return $app->redirect('/group/'.$this->parameters['group']->getSlugForURL());
		}

		$repo = new \repositories\UserNotificationPreferenceRepository();
		$this->parameters['preferences'] = array();
		foreach($app['extensions']->getExtensionsIncludingCore() as $extension) {
			if (!isset($this->parameters['preferences'][$extension->getId()])) {
				$this->parameters['preferences'][$extension->getId()] = array();
			}
			foreach($extension->getUserNotificationPreferenceTypes() as $type) {
				$userPref = $repo->load($app['currentUser'],$extension->getId() ,$type);
				$this->parameters['preferences'][$extension->getId()][$type] = array('email'=>$userPref->getIsEmail());
			}
		}
		
		return $app['twig']->render('site/group/watch.html.twig', $this->parameters);
	}

	function stopWatchingFromEmail($slug, $userid, $code,Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		$userRepo = new UserAccountRepository();
		$user = $userRepo->loadByID($userid);
		if (!$user) {
			$app['monolog']->addError("Failed stop watching group from email - no user ");
			die("NO"); // TODO
		}
		
		$userWatchesGroupStopRepo = new UserWatchesGroupStopRepository();
		$userWatchesGroupStop = $userWatchesGroupStopRepo->loadByUserAccountIDAndGroupIDAndAccessKey($user->getId(), $this->parameters['group']->getId(), $code);
		if (!$userWatchesGroupStop) {
			$app['monolog']->addError("Failed stop watching group from email - user ".$user->getId()." - code wrong");
			die("NO"); // TODO
		}
		
		$userWatchesGroupRepo = new UserWatchesGroupRepository();
		$userWatchesGroup = $userWatchesGroupRepo->loadByUserAndGroup($user, $this->parameters['group']);
		if (!$userWatchesGroup || !$userWatchesGroup->getIsWatching()) {
			$app['monolog']->addError("Failed stop watching group from email - user ".$user->getId()." - not watching");
			die("You don't watch this group"); // TODO
		}
		
		if ($request->request->get('action') == 'unwatch' && $request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$userWatchesGroupRepo->stopUserWatchingGroup($user, $this->parameters['group']);
			// redirect here because if we didn't the twig global and $app vars would be wrong (the old state)
			// this is an easy way to get round that.
			$app['flashmessages']->addMessage("You have stopped watching this group.");
			return $app->redirect('/group/'.$this->parameters['group']->getSlugForURL());
		}
		
		$this->parameters['user'] = $user;
		
		return $app['twig']->render('site/group/stopWatchingFromEmail.html.twig', $this->parameters);
		
	}
		
	function newImportURL($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		
		$importurl = new ImportURLModel();
		// we must setSiteId() here so loadClashForImportUrl() works
		$importurl->setSiteId($app['currentSite']->getId());
		$importurl->setGroupId($this->parameters['group']->getId());
		
		$form = $app['form.factory']->create(new ImportURLNewForm($app['currentSite'], $app['currentTimeZone']), $importurl);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$importURLRepository = new ImportURLRepository();
				
				$clash = $importURLRepository->loadClashForImportUrl($importurl);
				if ($clash) {
					$importurl->setIsEnabled(false);
					$app['flashmessages']->addMessage("There was a problem enabling this importer. Please try to enable it for details.");
				} else {
					$importurl->setIsEnabled(true);
				}
				
				$area = null;
				$areaRepository = new AreaRepository();
				$areasPost = $request->request->get('areas');
				if (is_array($areasPost)) {
					foreach ($areasPost as $areaCode) {
						if (substr($areaCode, 0, 9) == 'EXISTING:') {
							$area = $areaRepository->loadBySlug($app['currentSite'], substr($areaCode,9));
						}
					}
				}
				$importurl->setAreaId($area ? $area->getId() : null);
				
				$importURLRepository->create($importurl, $app['currentSite'], $app['currentUser']);
				
				return $app->redirect("/importurl/".$importurl->getSlug());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/group/newimporturl.html.twig', $this->parameters);
		
		
	}
	
}


