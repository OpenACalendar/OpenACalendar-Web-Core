<?php

namespace tests\repositories\builders;


use models\UserAccountModel;
use models\SiteModel;
use models\VenueModel;
use repositories\builders\VenueRepositoryBuilder;
use repositories\CountryRepository;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\VenueRepository;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventRecurSetModelGetNewMontlyEventsTest extends \BaseAppWithDBTest
{


	function testAddressCodeSearchRemoveSpaces() {
		$this->addCountriesToTestDB();

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
		$siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());

		$countryRepo = new CountryRepository();
		$gb = $countryRepo->loadByTwoCharCode('GB');

		$venue = new VenueModel();
		$venue->setTitle("test");
		$venue->setDescription("test test");
		$venue->setAddressCode("Eh1 2BR");
		$venue->setCountryId($gb->getId());

		$venueRepo = new VenueRepository();
		$venueRepo->create($venue, $site, $user);

		// Searching with remove spaces finds it!
		$vrb = new VenueRepositoryBuilder();
		$vrb->setFreeTextSearchAddressCode("EH12br", true);
		$venues = $vrb->fetchAll();

		$this->assertEquals(1, count($venues));

		// Searching with NO remove spaces is not found :-(
		$vrb = new VenueRepositoryBuilder();
		$vrb->setFreeTextSearchAddressCode("EH12br", false);
		$venues = $vrb->fetchAll();

		$this->assertEquals(0, count($venues));

		// And final check, searching for right spaces with No remove spaces will find it
		$vrb = new VenueRepositoryBuilder();
		$vrb->setFreeTextSearchAddressCode("EH1 2br", false);
		$venues = $vrb->fetchAll();

		$this->assertEquals(1, count($venues));

	}



}
