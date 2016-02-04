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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventHasTagTest extends \BaseAppWithDBTest {
	

	function testAddRemove() {

		$this->app['timesource']->mock(2013,7,1,7,0,0);
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2013,8,1,19,0,0));
		$event->setEndAt(getUTCDateTime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user);
		
		$tag = new TagModel();
		$tag->setTitle("test");
		
		$tagRepo = new TagRepository($this->app);
		$tagRepo->create($tag, $site, $user);
		
		## No tags
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));		
		
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsNotForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));		
		
		$eventRepoBuilder = new EventRepositoryBuilder($this->app);
		$eventRepoBuilder->setSite($site);
		$eventRepoBuilder->setTag($tag);
		$this->assertEquals(0, count($eventRepoBuilder->fetchAll()));
		
		## Add event to tag, test
		$tagRepo->addTagToEvent($tag, $event, $user);
		
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));		
		
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsNotForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));		
		
		$eventRepoBuilder = new EventRepositoryBuilder($this->app);
		$eventRepoBuilder->setSite($site);
		$eventRepoBuilder->setTag($tag);
		$this->assertEquals(1, count($eventRepoBuilder->fetchAll()));
		
		## remove tag
		$tagRepo->removeTagFromEvent($tag, $event, $user);
		
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));		
		
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsNotForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));		
		
		$eventRepoBuilder = new EventRepositoryBuilder($this->app);
		$eventRepoBuilder->setSite($site);
		$eventRepoBuilder->setTag($tag);
		$this->assertEquals(0, count($eventRepoBuilder->fetchAll()));
		
	}
	
	
	
	function testAddOnCreate() {

		$this->app['timesource']->mock(2013,7,1,7,0,0);
		
		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		$site = new SiteModel();
		$site->setTitle("Test");
		$site->setSlug("test");
		
		$siteRepo = new SiteRepository($this->app);
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());
		
		$tag = new TagModel();
		$tag->setTitle("test");
		
		$tagRepo = new TagRepository($this->app);
		$tagRepo->create($tag, $site, $user);
		
		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2013,8,1,19,0,0));
		$event->setEndAt(getUTCDateTime(2013,8,1,21,0,0));

		$eventRepository = new EventRepository($this->app);
		$eventRepository->create($event, $site, $user, null, null, null, array ($tag));
		
		## test		
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsForEvent($event);
		$this->assertEquals(1, count($tagRepoBuilder->fetchAll()));		
		
		$tagRepoBuilder = new TagRepositoryBuilder($this->app);
		$tagRepoBuilder->setSite($site);
		$tagRepoBuilder->setTagsNotForEvent($event);
		$this->assertEquals(0, count($tagRepoBuilder->fetchAll()));		
		
		$eventRepoBuilder = new EventRepositoryBuilder($this->app);
		$eventRepoBuilder->setSite($site);
		$eventRepoBuilder->setTag($tag);
		$this->assertEquals(1, count($eventRepoBuilder->fetchAll()));
		
		
	}
	
	
	
	
}




