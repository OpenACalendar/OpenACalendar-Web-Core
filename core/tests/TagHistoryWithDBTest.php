<?php

use models\TagHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\TagModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\TagRepository;
use repositories\TagHistoryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TagHistoryTest extends \BaseAppWithDBTest {

	
	function testIntegration1() {
		\TimeSource::mock(2014, 1, 1, 12, 0, 0);
		
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
		
		## Create tag
		\TimeSource::mock(2014, 1, 1, 13, 0, 0);
		$tag = new TagModel();
		$tag->setTitle("test");
		$tag->setDescription("test test");
		
		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);
		
		## Edit tag
		\TimeSource::mock(2014, 1, 1, 14, 0, 0);
		
		$tag = $tagRepo->loadById($tag->getId());
		$tag->setDescription("testy");
		$tagRepo->edit($tag, $user);
		
		## Now save changed flags on these .....
		$tagHistoryRepo = new TagHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM tag_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$tagHistory = new TagHistoryModel();
			$tagHistory->setFromDataBaseRow($data);
			$tagHistoryRepo->ensureChangedFlagsAreSet($tagHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setTag($tag);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeVenueHistory(false);
		$historyRepo->setIncludeGroupHistory(true);
		$historyRepo->setIncludeTagHistory(true);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(true, $histories[0]->getDescriptionChanged());
		$this->assertEquals(false, $histories[0]->getIsDeletedChanged());		
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());

				
		
	}

	function testIntegration2() {
		\TimeSource::mock(2014, 1, 1, 12, 0, 0);

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

		## Create tag
		\TimeSource::mock(2014, 1, 1, 13, 0, 0);
		$tag = new TagModel();
		$tag->setTitle("test");
		$tag->setDescription("test test");

		$tagRepo = new TagRepository();
		$tagRepo->create($tag, $site, $user);

		## Delete tag
		\TimeSource::mock(2014, 1, 1, 14, 0, 0);

		$tag = $tagRepo->loadById($tag->getId());
		$tagRepo->delete($tag, $user);

		## Now save changed flags on these .....
		$tagHistoryRepo = new TagHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM tag_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$tagHistory = new TagHistoryModel();
			$tagHistory->setFromDataBaseRow($data);
			$tagHistoryRepo->ensureChangedFlagsAreSet($tagHistory);
		}

		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder();
		$historyRepo->setTag($tag);
		$historyRepo->setIncludeEventHistory(false);
		$historyRepo->setIncludeVenueHistory(false);
		$historyRepo->setIncludeGroupHistory(true);
		$historyRepo->setIncludeTagHistory(true);
		$histories = $historyRepo->fetchAll();

		$this->assertEquals(2, count($histories));

		#the Delete
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(false, $histories[0]->getDescriptionChanged());
		$this->assertEquals(true, $histories[0]->getIsDeletedChanged());

		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(true, $histories[1]->getDescriptionChanged());
		$this->assertEquals(false, $histories[1]->getIsDeletedChanged());



	}

	
}

