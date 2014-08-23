<?php

use models\UserAccountModel;
use models\SiteModel;
use models\VenueModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\VenueRepository;
use repositories\CountryRepository;
use repositories\builders\VenueRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class VenueDeleteTest extends \PHPUnit_Framework_TestCase {
	
	function test1() {
		\TimeSource::mock(2014,1,1,0,0,0);

		$DB = getNewTestDB();
		addCountriesToTestDB();

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
		
		$countryRepo = new CountryRepository();
		$gb = $countryRepo->loadByTwoCharCode('GB');

		$venue = new VenueModel();
		$venue->setTitle("test");
		$venue->setDescription("test test");
		$venue->setCountryId($gb->getId());

		\TimeSource::mock(2014,1,1,1,0,0);
		$venueRepo = new VenueRepository();
		$venueRepo->create($venue, $site, $user);

		\TimeSource::mock(2014,1,1,2,0,0);
		$venueRepo->delete($venue, $user);


		$this->checkVenueInTest1($venueRepo->loadById($venue->getId()));
		$this->checkVenueInTest1($venueRepo->loadBySlug($site, $venue->getSlug()));
		
		$vrb = new VenueRepositoryBuilder();
		$vrb->setIncludeDeleted(true);
		$this->assertEquals(1, count($vrb->fetchAll()));

		$vrb = new VenueRepositoryBuilder();
		$vrb->setIncludeDeleted(false);
		$this->assertEquals(0, count($vrb->fetchAll()));

	}
	
	protected function checkVenueInTest1(VenueModel $venue) {
		$this->assertEquals("test test", $venue->getDescription());
		$this->assertEquals("test", $venue->getTitle());
		$this->assertNotNull($venue->getCountryId());
	}
	
}




