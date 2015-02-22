<?php
use models\SiteModel;
use models\UserAccountModel;
use usernotifications\notifycontent\UserWatchesAreaNotifyContent;
use tasks\SendUserWatchesNotifyTask;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TestSendUserWatchesNotifyTask extends SendUserWatchesNotifyTask {
	public function testGetEmailSubject(SiteModel $siteModel, UserAccountModel $userAccountModel, $contentsToSend) {
		return $this->getEmailSubject($siteModel, $userAccountModel, $contentsToSend);
	}
}

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesNotifyTaskTest  extends \PHPUnit_Framework_TestCase {



	public function testGetSubjectOneData() {
		global $app;

		$site = new \models\SiteModel();
		$site->setTitle("Test");

		$user = new \models\UserAccountModel();
		$user->setUsername("test");
		$user->setEmail("test@test.com");

		$content1 = new UserWatchesAreaNotifyContent();
		$content1->setWatchedThingTitle("Edinburgh");

		$task = new TestSendUserWatchesNotifyTask($app);
		$this->assertEquals("Changes in Edinburgh", $task->testGetEmailSubject($site, $user, array( $content1 )));

	}

	public function testGetSubjectTwoData() {
		global $app;

		$site = new \models\SiteModel();
		$site->setTitle("Test");

		$user = new \models\UserAccountModel();
		$user->setUsername("test");
		$user->setEmail("test@test.com");

		$content1 = new UserWatchesAreaNotifyContent();
		$content1->setWatchedThingTitle("Edinburgh");

		$content2 = new UserWatchesAreaNotifyContent();
		$content2->setWatchedThingTitle("Glasgow");

		$task = new TestSendUserWatchesNotifyTask($app);
		$this->assertEquals("Changes in Edinburgh and Glasgow", $task->testGetEmailSubject($site, $user, array( $content1, $content2 )));

	}

	public function testGetSubjectThreeData() {
		global $app;

		$site = new \models\SiteModel();
		$site->setTitle("Test");

		$user = new \models\UserAccountModel();
		$user->setUsername("test");
		$user->setEmail("test@test.com");

		$content1 = new UserWatchesAreaNotifyContent();
		$content1->setWatchedThingTitle("Edinburgh");

		$content2 = new UserWatchesAreaNotifyContent();
		$content2->setWatchedThingTitle("Glasgow");

		$content3 = new UserWatchesAreaNotifyContent();
		$content3->setWatchedThingTitle("Aberdeen");

		$task = new TestSendUserWatchesNotifyTask($app);
		$this->assertEquals("Changes in Test", $task->testGetEmailSubject($site, $user, array( $content1, $content2 , $content3 )));

	}

}


