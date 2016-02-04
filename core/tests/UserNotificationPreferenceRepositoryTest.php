<?php

use models\UserAccountModel;
use repositories\UserAccountRepository;
use repositories\UserNotificationPreferenceRepository;

/**
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserNotificationPreferenceRepositoryTest extends \BaseAppWithDBTest {
	
	function testGetDefault() {

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		
		$prefRepo = new UserNotificationPreferenceRepository($this->app);
		
		### Test
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchPrompt');
		$this->assertEquals(true, $pref->getIsEmail());
		
		
	}
	
	
	function testSetOldWayFalseThenGet() {


		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		
		$prefRepo = new UserNotificationPreferenceRepository($this->app);
		
		### Set
		$stat = $this->app['db']->prepare("ALTER TABLE user_account_information ADD is_email_watch_notify boolean default '0' NOT NULL");
		$stat->execute();
		$stat = $this->app['db']->prepare("ALTER TABLE user_account_information ADD is_email_watch_prompt boolean default '0' NOT NULL");
		$stat->execute();
		
		### Test
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchPrompt');
		$this->assertEquals(false, $pref->getIsEmail());
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchNotify');
		$this->assertEquals(false, $pref->getIsEmail());
		
	}
	
	function testSetOldWayTrueThenGet() {
			

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		
		$prefRepo = new UserNotificationPreferenceRepository($this->app);
		
		### Set
		$stat = $this->app['db']->prepare("ALTER TABLE user_account_information ADD is_email_watch_notify boolean default '1' NOT NULL");
		$stat->execute();
		$stat = $this->app['db']->prepare("ALTER TABLE user_account_information ADD is_email_watch_prompt boolean default '1' NOT NULL");
		$stat->execute();
		
		### Test
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchPrompt');
		$this->assertEquals(true, $pref->getIsEmail());
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchNotify');
		$this->assertEquals(true, $pref->getIsEmail());
		
	}
	
	function testSetThenGet() {
			

		$user = new UserAccountModel();
		$user->setEmail("test@jarofgreen.co.uk");
		$user->setUsername("test");
		$user->setPassword("password");
		
		$userRepo = new UserAccountRepository($this->app);
		$userRepo->create($user);
		
		
		$prefRepo = new UserNotificationPreferenceRepository($this->app);
		
		### Set
		$prefRepo->editEmailPreference($user, 'org.openacalendar', 'WatchPrompt', true);
		
		### Test
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchPrompt');
		$this->assertEquals(true, $pref->getIsEmail());
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchNotify');
		$this->assertEquals(true, $pref->getIsEmail());
		
		### Set
		$prefRepo->editEmailPreference($user, 'org.openacalendar', 'WatchPrompt', false);
		
		### Test
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchPrompt');
		$this->assertEquals(false, $pref->getIsEmail());
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchNotify');
		$this->assertEquals(true, $pref->getIsEmail());
		
		
		### Set
		$prefRepo->editEmailPreference($user, 'org.openacalendar', 'WatchNotify', false);
		
		### Test
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchPrompt');
		$this->assertEquals(false, $pref->getIsEmail());
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchNotify');
		$this->assertEquals(false, $pref->getIsEmail());

		
		
		### Set
		$prefRepo->editEmailPreference($user, 'org.openacalendar', 'WatchPrompt', true);
		
		### Test
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchPrompt');
		$this->assertEquals(true, $pref->getIsEmail());
		$pref = $prefRepo->load($user, 'org.openacalendar', 'WatchNotify');
		$this->assertEquals(false, $pref->getIsEmail());
		
		
	}
	
	
	
	
}

