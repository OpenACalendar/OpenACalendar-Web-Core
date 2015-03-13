<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use models\TagModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\TagRepository;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagPurgeTest extends \BaseAppWithDBTest {
	
	
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
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0,'Europe/London'));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0,'Europe/London'));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$tag = new TagModel();
		$tag->setTitle("Test");
		
		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);
		$tagRepo->addTagToEvent($tag, $event, $user);
				
		## Test
		$this->assertNotNull($tagRepo->loadById($tag->getId()));
		
		$tagRepoBuilder = new repositories\builders\TagRepositoryBuilder();
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));
								
		## Purge!
		$tagRepo->purge($tag);		
				
		## Test
		$this->assertNull($tagRepo->loadById($tag->getId()));
				
		$tagRepoBuilder = new repositories\builders\TagRepositoryBuilder();
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));
								
		
		
		
	}
	
	
}
	