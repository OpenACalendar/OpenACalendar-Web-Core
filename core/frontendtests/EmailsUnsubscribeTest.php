<?php


use Facebook\WebDriver\WebDriverBy;
use models\SiteModel;
use models\UserAccountModel;
use repositories\SiteRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserAccountRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class EmailsUnsubscribeTest extends BaseFrontEndTest {

    function testUnsubcribeManually() {

        $this->addCountriesToTestDB();

        $user = new UserAccountModel();
        $user->setEmail("test@jarofgreen.co.uk");
        $user->setUsername("test");
        $user->setPassword("password");

        $userRepo = new UserAccountRepository($this->app);
        $userRepo->create($user);

        $site = new SiteModel();
        $site->setTitle("Test1");
        $site->setSlug("test1");
        $site->setIsWebRobotsAllowed(false);

        $siteRepo = new SiteRepository($this->app);
        $siteRepo->create($site, $user, array(), $this->getSiteQuotaUsedForTesting());


        $countryRepo = new \repositories\CountryRepository($this->app);
        $countryInSiteRepo = new \repositories\CountryInSiteRepository($this->app);
        $countryInSiteRepo->addCountryToSite($countryRepo->loadByTwoCharCode('GB'), $site, $user);

        $userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($this->app);
        $userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);

        $userNotificationPreferenceRepository = new \repositories\UserNotificationPreferenceRepository($this->app);

        // Test User Email Settings Currently

        $user = $userRepo->loadByEmail('test@jarofgreen.co.uk');
        $this->assertEquals('w', $user->getEmailUpcomingEvents());
        $this->assertEquals(1, $user->getEmailUpcomingEventsDaysNotice());

        $this->assertTrue($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'WatchPrompt')->getIsEmail());
        $this->assertTrue($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'WatchNotify')->getIsEmail());
        $this->assertTrue($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'UpcomingEvents')->getIsEmail());
        $this->assertTrue($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'WatchImportExpired')->getIsEmail());

        // Follow Link and change them!

        $this->driver->get('http://openadevcalendar.co.uk:8082/you/emails/'.
                           $user->getId().'/'.$userAccountGeneralSecurityKey->getAccessKey());

        sleep($this->sleepOnActionWithNetwork);

        $this->driver->findElement(WebDriverBy::id('UserEmailsForm_email_upcoming_events_0'))->click();

        $this->driver->findElement(WebDriverBy::id('UserEmailsForm_email_upcoming_events_days_notice'))->sendKeys('0');

        $this->driver->findElement(WebDriverBy::id('UserEmailsForm_org_openacalendar_WatchPrompt'))->click();
        $this->driver->findElement(WebDriverBy::id('UserEmailsForm_org_openacalendar_WatchNotify'))->click();
        $this->driver->findElement(WebDriverBy::id('UserEmailsForm_org_openacalendar_UpcomingEvents'))->click();
        $this->driver->findElement(WebDriverBy::id('UserEmailsForm_org_openacalendar_WatchImportExpired'))->click();
        
        $submitLink = $this->driver->findElement(WebDriverBy::cssSelector('form.styled input[type="submit"]'));
        $submitLink->click();

        // Test User Email Settings Now

        $user = $userRepo->loadByEmail('test@jarofgreen.co.uk');
        $this->assertEquals('n', $user->getEmailUpcomingEvents());
        $this->assertEquals(10, $user->getEmailUpcomingEventsDaysNotice());

        $this->assertFalse($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'WatchPrompt')->getIsEmail());
        $this->assertFalse($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'WatchNotify')->getIsEmail());
        $this->assertFalse($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'UpcomingEvents')->getIsEmail());
        $this->assertFalse($userNotificationPreferenceRepository->load($user, 'org.openacalendar', 'WatchImportExpired')->getIsEmail());

    }

}
