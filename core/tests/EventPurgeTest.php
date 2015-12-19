<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use org\openacalendar\curatedlists\models\CuratedListModel;
use models\TagModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use org\openacalendar\curatedlists\repositories\CuratedListRepository;
use repositories\TagRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventPurgeTest extends \BaseAppWithDBTest {
	

	function __construct()
	{
		$this->extensions = array('CuratedLists');
	}

	
	function test1() {

		TimeSource::mock(2014,5,1,7,0,0);
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository();
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository();
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());
		
		$group = new GroupModel();
		$group->setTitle("test");
		$group->setDescription("test test");
		$group->setUrl("http://www.group.com");
		
		$groupRepo = new GroupRepository();
		$groupRepo->create($group, $site, $user);

		$eventDraft = new \models\NewEventDraftModel();
		$eventDraft->setSiteId($site->getId());

		$eventDraftRepo = new \repositories\NewEventDraftRepository();
		$eventDraftRepo->create($eventDraft);

		TimeSource::mock(2014,5,1,8,0,0);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		$eventMeta = new \models\EventEditMetaDataModel();
		$eventMeta->setCreatedFromNewEventDraftID($eventDraft->getId());
		$eventMeta->setUserAccount($user);

		$eventDupe = new EventModel();
		$eventDupe->setSummary("test");
		$eventDupe->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$eventDupe->setEndAt(getUTCDateTime(2014,5,10,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->createWithMetaData($event, $site, $eventMeta, $group);
		$eventRepository->create($eventDupe, $site, $user, $group);
		TimeSource::mock(2014,5,1,7,1,0);
		$eventRepository->markDuplicate($eventDupe, $event);

		$userAtEvent = new \models\UserAtEventModel();
		$userAtEvent->setEventId($event->getId());
		$userAtEvent->setUserAccountId($user->getId());
		$userAtEvent->setIsPlanAttending(true);

		$userAtEventRepo = new \repositories\UserAtEventRepository();
		$userAtEventRepo->create($userAtEvent);

		$curatedList = new CuratedListModel();
		$curatedList->setTitle("test");
		$curatedList->setDescription("test this!");
		
		$clRepo = new CuratedListRepository();
		$clRepo->create($curatedList, $site, $user);
		$clRepo->addEventtoCuratedList($event, $curatedList, $user);
		
		$tag = new TagModel();
		$tag->setTitle("Test");
		
		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);
		$tagRepo->addTagToEvent($tag, $event, $user);

		$sysadminCommentRepo = new \repositories\SysAdminCommentRepository();
		$sysadminCommentRepo->createAboutEvent($event, "TEST", null);

		$media = new \models\MediaModel();
		$media->setSiteId($site->getId());

		$mediaRepo = new \repositories\MediaRepository();
		$mediaRepo->create($media, $user);

		$mediaInEventRepo = new \repositories\MediaInEventRepository();
		$mediaInEventRepo->add($media, $event, $user);

		## TEST
		$this->assertNotNull($eventRepository->loadBySlug($site, $event->getSlug()));
		
		## PURGE!
		$eventRepository->purge($event);
		
		## TEST
		$this->assertNull($eventRepository->loadBySlug($site, $event->getSlug()));
		
		
	}
	
	
	
}




