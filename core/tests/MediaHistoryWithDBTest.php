<?php

use models\MediaHistoryModel;
use models\UserAccountModel;
use models\SiteModel;
use models\MediaModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\MediaRepository;
use repositories\MediaHistoryRepository;
use \repositories\builders\HistoryRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class MediaHistoryWithDBTest extends \BaseAppWithDBTest {

	
	function testIntegration1() {
		$this->app['timesource']->mock(2014, 1, 1, 12, 0, 0);
		
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
		
		## Create media
		$this->app['timesource']->mock(2014, 1, 1, 13, 0, 0);
		$media = new MediaModel();
		$media->setTitle("test");
		$media->setSourceText("found on Google");
		$media->setSourceUrl(null);
		
		$mediaRepo = new MediaRepository($this->app);
		$mediaRepo->create($media, $user);
		
		## Edit media
		$this->app['timesource']->mock(2014, 1, 1, 14, 0, 0);
		
		$media = $mediaRepo->loadById($media->getId());
		$media->setSourceUrl("http://www.mediasource.com");
		$media->setSourceText('Media Source');

		$metaEdit = new \models\MediaEditMetaDataModel();
		$metaEdit->setUserAccount($user);
		$mediaRepo->editWithMetaData($media, $metaEdit);
		
		## Now save changed flags on these .....
		$mediaHistoryRepo = new MediaHistoryRepository($this->app);
		$stat = $this->app['db']->prepare("SELECT * FROM media_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$mediaHistory = new MediaHistoryModel();
			$mediaHistory->setFromDataBaseRow($data);
			$mediaHistoryRepo->ensureChangedFlagsAreSet($mediaHistory);
		}
		
		## Now load and check
		$historyRepo = new HistoryRepositoryBuilder($this->app);
		$historyRepo->getHistoryRepositoryBuilderConfig()->setMedia($media);
		$histories = $historyRepo->fetchAll();
		
		$this->assertEquals(2, count($histories));
		
		#the edit
		$this->assertEquals(FALSE, $histories[0]->getTitleChanged());
		$this->assertEquals(true, $histories[0]->getSourceURLChanged());
		$this->assertEquals(true, $histories[0]->getSourceTextChanged());
				
		#the create
		$this->assertEquals(true, $histories[1]->getTitleChanged());
		$this->assertEquals(false, $histories[1]->getSourceURLChanged());
		$this->assertEquals(true, $histories[1]->getSourceTextChanged());

				
		
	}

}

