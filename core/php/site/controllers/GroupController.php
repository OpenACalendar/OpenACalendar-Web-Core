<?php

namespace site\controllers;

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
use repositories\builders\CuratedListRepositoryBuilder;
use site\forms\ImportURLNewForm;
use JMBTechnologyLimited\ParseDateTimeRangeString\ParseDateTimeRangeString;

use repositories\builders\filterparams\EventFilterParams;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupController {
	
	
	protected $parameters = array();
	
	protected function build($slug, Request $request, Application $app) {
		global $CONFIG;
		$this->parameters = array('currentUserWatchesGroup'=>false);
		
		if (strpos($slug, "-")) {
			$slug = array_shift(explode("-", $slug, 2));
		}
		
		$gr = new GroupRepository();
		$this->parameters['group'] = $gr->loadBySlug($app['currentSite'], $slug);
		if (!$this->parameters['group']) {
			return false;
		}
		
		if (userGetCurrent()) {
			$uwgr = new UserWatchesGroupRepository();
			$uwg = $uwgr->loadByUserAndGroup(userGetCurrent(), $this->parameters['group']);
			$this->parameters['currentUserWatchesGroup'] = $uwg && $uwg->getIsWatching();
		}


		$app['currentUserActions']->set("org.openacalendar","groupHistory",true);
		$app['currentUserActions']->set("org.openacalendar","groupEditDetails",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeatureGroup()
			&& !$this->parameters['group']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","groupEditMedia",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
			&& $app['currentSite']->getIsFeatureGroup()
			&& !$this->parameters['group']->getIsDeleted());
		$app['currentUserActions']->set("org.openacalendar","groupNewEvent",
			$app['currentUserPermissions']->hasPermission("org.openacalendar","CALENDAR_CHANGE")
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
		if (userGetCurrent()) {
			$this->parameters['eventListFilterParams']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
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


		if (userGetCurrent()) {
			$clrb = new CuratedListRepositoryBuilder();
			$clrb->setSite($app['currentSite']);
			$clrb->setUserCanEdit(userGetCurrent());
			$clrb->setIncludeDeleted(false);
			$clrb->setGroupInformation($this->parameters['group']);
			$this->parameters['curatedListsUserCanEdit'] = $clrb->fetchAll();
		} else {
			$this->parameters['curatedListsUserCanEdit'] = array();
		}

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
	
	
	function media($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		if ($app['currentUserCanEditSite'] && $app['currentSite']->getIsFeatureGroup()) {
			
			
			$form = $app['form.factory']->create(new UploadNewMediaForm());
		
			
			if ('POST' == $request->getMethod()) {
				$form->bind($request);

				if ($form->isValid()) {

					$mediaRepository = new MediaRepository();
					$media = $mediaRepository->createFromFile($form['media']->getData(), $app['currentSite'], userGetCurrent(),
							$form['title']->getData(),$form['source_text']->getData(),$form['source_url']->getData());
					
					if ($media) {

						$mediaInGroupRepo = new MediaInGroupRepository();
						$mediaInGroupRepo->add($media, $this->parameters['group'], userGetCurrent());
						
						$app['flashmessages']->addMessage('Picture added!');
						return $app->redirect("/group/".$this->parameters['group']->getSlug());
						
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
		
		return $app['twig']->render('site/group/media.html.twig', $this->parameters);
	}
	
	function mediaRemove($slug, $mediaslug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $mediaslug);
			if ($media) {
				$mediaInGroupRepo = new MediaInGroupRepository();
				$mediaInGroupRepo->remove($media, $this->parameters['group'], userGetCurrent());
				$app['flashmessages']->addMessage('Removed!');
			}
		}
		
		return $app->redirect("/group/".$this->parameters['group']->getSlug().'/media');
	}
	
	function mediaAddExisting($slug, Request $request, Application $app) {		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
			
		if ($request->request->get('CSFRToken') == $app['websession']->getCSFRToken()) {
			$mediaRepository = new MediaRepository();
			$media = $mediaRepository->loadBySlug($app['currentSite'], $request->request->get('addMedia'));
			if ($media) {
				$mediaInGroupRepo = new MediaInGroupRepository();
				$mediaInGroupRepo->add($media, $this->parameters['group'], userGetCurrent());
				$app['flashmessages']->addMessage('Added!');
				return $app->redirect("/group/".$this->parameters['group']->getSlug().'/');
			}
		}
		
		$mrb = new MediaRepositoryBuilder();
		$mrb->setIncludeDeleted(false);
		$mrb->setSite($app['currentSite']);
		$mrb->setNotInGroup($this->parameters['group']);
		$this->parameters['medias'] = $mrb->fetchAll();
		
		return $app['twig']->render('site/group/media.add.existing.html.twig', $this->parameters);
	}
	
	
	function edit($slug, Request $request, Application $app) {

		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		if ($this->parameters['group']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		
		$form = $app['form.factory']->create(new GroupEditForm(), $this->parameters['group']);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$groupRepository = new GroupRepository();
				$groupRepository->edit($this->parameters['group'], userGetCurrent());
				
				return $app->redirect("/group/".$this->parameters['group']->getSlugForUrl());
				
			}
		}
		
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/group/edit.html.twig', $this->parameters);
		
	}	
	
	function newEvent($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}

		if ($this->parameters['group']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		return $app['twig']->render('site/group/newEvent.html.twig', $this->parameters);
		
	}
	
	function newEventGo($slug, Request $request, Application $app) {
		
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
		
		if ($this->parameters['group']->getIsDeleted()) {
			die("No"); // TODO
		}
		
		$parseResult = null;
		
		$event = new EventModel();
		$event->setDefaultOptionsFromSite($app['currentSite']);
		if (isset($_GET['what']) && trim($_GET['what'])) {
			$event->setSummary($_GET['what']);
		}
		if (isset($_GET['when']) && trim($_GET['when'])) {
			$parse = new ParseDateTimeRangeString(\TimeSource::getDateTime(), $app['currentTimeZone']);
			$parseResult = $parse->parse($_GET['when']);
			$event->setStartAt($parseResult->getStart());
			$event->setEndAt($parseResult->getEnd());
		} else if (isset($_GET['date']) && trim($_GET['date'])) {
			$bits = explode("-", $_GET['date']);
			if (count($bits) == 3 && intval($bits[0]) && intval($bits[1]) && intval($bits[2])) {
				$start = \TimeSource::getDateTime();
				$start->setTimezone(new \DateTimeZone($app['currentTimeZone']));
				$start->setDate($bits[0], $bits[1], $bits[2]);
				$start->setTime(9, 0, 0);
				$event->setStartAt($start);
				$end = clone $start;
				$end->setTime(17, 0, 0);
				$event->setEndAt($end);
			}
		}
		
		$form = $app['form.factory']->create(new EventNewForm($app['currentSite'], $app['currentTimeZone']), $event);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				
				$eventRepository = new EventRepository();
				$eventRepository->create($event, $app['currentSite'], userGetCurrent(), $this->parameters['group']);
				
				
				if ($parseResult && $app['config']->logFileParseDateTimeRange && 
						($parseResult->getStart()->getTimestamp() != $event->getStartAt()->getTimestamp() 
						|| $parseResult->getEnd()->getTimestamp() != $event->getEndAt()->getTimestamp())) {
					
					$handle = fopen($app['config']->logFileParseDateTimeRange, "a");
					$now = \TimeSource::getDateTime();
					fwrite($handle, 'Site, '.$app['currentSite']->getId()." ,". $app['currentSite']->getSlug()." ,".
							'Event,'.$event->getSlug()." ,".
							'Now,'.$now->format("c") . "," . 
							'Wanted Start,'.$event->getStartAt()->format("c") . " ," . 
							'Wanted End,'.$event->getEndAt()->format("c") . " ," . 
							'Typed,'.str_replace("\n", " ", $_GET['when']) . "\n");
					fclose($handle);
					
				}
				
				
				if ($event->getIsPhysical()) {
					return $app->redirect("/event/".$event->getSlug().'/edit/venue');
				} else {
					return $app->redirect("/event/".$event->getSlug());
				}
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		return $app['twig']->render('site/group/newEventGo.html.twig', $this->parameters);
	}

	
	function calendarNow($slug, Request $request, Application $app) {
		if (!$this->build($slug, $request, $app)) {
			$app->abort(404, "Group does not exist.");
		}
				
		$this->parameters['calendar'] = new \RenderCalendar();
		$this->parameters['calendar']->getEventRepositoryBuilder()->setSite($app['currentSite']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setGroup($this->parameters['group']);
		$this->parameters['calendar']->getEventRepositoryBuilder()->setIncludeDeleted(false);
		if (userGetCurrent()) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
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
		if (userGetCurrent()) {
			$this->parameters['calendar']->getEventRepositoryBuilder()->setUserAccount(userGetCurrent(), true);
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
				$repo->startUserWatchingGroup(userGetCurrent(), $this->parameters['group']);
				$app['flashmessages']->addMessage("Watching!");
			} else if ($request->request->get('action') == 'unwatch') {
				$repo->stopUserWatchingGroup(userGetCurrent(), $this->parameters['group']);
				$app['flashmessages']->addMessage("No longer watching");
			}
			// redirect here because if we didn't the  $this->parameters vars would be wrong (the old state)
			// this is an easy way to get round that. Also it's nice UI to go back to the group page.
			return $app->redirect('/group/'.$this->parameters['group']->getSlug());
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
			return $app->redirect('/group/'.$this->parameters['group']->getSlug());
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
				
				$importURLRepository->create($importurl, $app['currentSite'], userGetCurrent());
				
				return $app->redirect("/importurl/".$importurl->getSlug());
				
			}
		}
		
		$this->parameters['form'] = $form->createView();
		
		return $app['twig']->render('site/group/newimporturl.html.twig', $this->parameters);
		
		
	}
	
}


