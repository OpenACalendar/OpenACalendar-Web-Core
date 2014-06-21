<?php

use models\UserAccountModel;
use models\SiteModel;
use models\TagModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\TagRepository;
use repositories\EventRepository;
use repositories\builders\EventRepositoryBuilder;
use repositories\builders\TagRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventHasTagTest extends \PHPUnit_Framework_TestCase {
	

	function testAddRemove() {
		$DB = getNewTestDB();

		TimeSource::mock(2013,7,1,7,0,0);
		
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
		$siteRepo->create($site, $user, array(), getSiteQuotaUsedForTesting());
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2013,8,1,19,0,0));
		$event->setEndAt(getUTCDateTime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);
		
		$tag = new TagModel();
		$tag->setTitle("test");
		
		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);
		
		## No tags
		$tagRepoBuilder = new TagRepositoryBuilder();
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));		
		
		$tagRepoBuilder = new TagRepositoryBuilder();
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsNotForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));		
		
		$eventRepoBuilder = new EventRepositoryBuilder();
		$eventRepoBuilder->setSite($site);
		$eventRepoBuilder->setTag($tag);
		$this->assertEquals(0, count($eventRepoBuilder->fetchAll()));
		
		## Add event to tag, test
		$tagRepo->addTagToEvent($tag, $event, $user);
		
		$tagRepoBuilder = new TagRepositoryBuilder();
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));		
		
		$tagRepoBuilder = new TagRepositoryBuilder();
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsNotForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));		
		
		$eventRepoBuilder = new EventRepositoryBuilder();
		$eventRepoBuilder->setSite($site);
		$eventRepoBuilder->setTag($tag);
		$this->assertEquals(1, count($eventRepoBuilder->fetchAll()));
		
		## remove tag
		$tagRepo->removeTagFromEvent($tag, $event, $user);
		
		$tagRepoBuilder = new TagRepositoryBuilder();
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));		
		
		$tagRepoBuilder = new TagRepositoryBuilder();
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsNotForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));		
		
		$eventRepoBuilder = new EventRepositoryBuilder();
		$eventRepoBuilder->setSite($site);
		$eventRepoBuilder->setTag($tag);
		$this->assertEquals(0, count($eventRepoBuilder->fetchAll()));
		
	}
	
	
	
	
	
}




