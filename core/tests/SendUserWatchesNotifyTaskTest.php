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
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TestSendUserWatchesNotifyTask extends tasks\SendUserWatchesNotifyTask {
	public function testGetEmailSubject(SiteModel $siteModel, UserAccountModel $userAccountModel, $contentsToSend) {
		return $this->getEmailSubject($siteModel, $userAccountModel, $contentsToSend);
	}
	public function testGetNewAndHistoriesForContentsToSend($contentsToSend) {
		return $this->getNewAndHistoriesForContentsToSend($contentsToSend);
	}
}

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendUserWatchesNotifyTaskTest  extends \BaseAppTest {



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

	public function testGetNewAndHistoriesForContentsToSendSimple() {
		global $app;

		$eventHistoryModel = new \models\EventHistoryModel();
		$eventHistoryModel->setFromDataBaseRow(array(
			'event_id'=>1,
			'summary'=>null,
			'description'=>null,
			'start_at'=>null,
			'end_at'=>null,
			'created_at'=>'2014-01-01 10:10:10',
			'is_deleted'=>null,
			'is_cancelled'=>null,
			'country_id'=>null,
			'timezone'=>null,
			'venue_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'is_virtual'=>null,
			'is_physical'=>null,
			'area_id'=>null,
			'user_account_id'=>null,
			'summary_changed'=>'-1',
			'description_changed'=>'1',
			'start_at_changed'=>'-1',
			'end_at_changed'=>'-1',
			'is_deleted_changed'=>'-1',
			'country_id_changed'=>'-1',
			'timezone_changed'=>'-1',
			'venue_id_changed'=>'-1',
			'url_changed'=>'-1',
			'is_virtual_changed'=>'-1',
			'is_physical_changed'=>'-1',
			'area_id_changed'=>'-1',
			'is_new'=>'-1',
			'custom_fields'=>null,
			'custom_fields_changed'=>null,
		));

		$content1 = new UserWatchesAreaNotifyContent();
		$content1->setWatchedThingTitle("Edinburgh");
		$content1->setHistories(array($eventHistoryModel));


		$task = new TestSendUserWatchesNotifyTask($app);
		list($newThings, $histories) = $task->testGetNewAndHistoriesForContentsToSend(array($content1));


		$this->assertEquals(1, count($histories));
		$this->assertEquals($eventHistoryModel, $histories[0]);


	}

	public function testGetNewAndHistoriesForContentsToSendSort() {
		global $app;

		$eventHistoryModel1 = new \models\EventHistoryModel();
		$eventHistoryModel1->setFromDataBaseRow(array(
			'event_id'=>1,
			'summary'=>null,
			'description'=>null,
			'start_at'=>null,
			'end_at'=>null,
			'created_at'=>'2014-01-01 10:10:10',
			'is_deleted'=>null,
			'is_cancelled'=>null,
			'country_id'=>null,
			'timezone'=>null,
			'venue_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'is_virtual'=>null,
			'is_physical'=>null,
			'area_id'=>null,
			'user_account_id'=>null,
			'summary_changed'=>'-1',
			'description_changed'=>'1',
			'start_at_changed'=>'-1',
			'end_at_changed'=>'-1',
			'is_deleted_changed'=>'-1',
			'country_id_changed'=>'-1',
			'timezone_changed'=>'-1',
			'venue_id_changed'=>'-1',
			'url_changed'=>'-1',
			'is_virtual_changed'=>'-1',
			'is_physical_changed'=>'-1',
			'area_id_changed'=>'-1',
			'is_new'=>'-1',
			'custom_fields'=>null,
			'custom_fields_changed'=>null,
		));

		$content1 = new UserWatchesAreaNotifyContent();
		$content1->setWatchedThingTitle("Edinburgh");
		$content1->setHistories(array($eventHistoryModel1));


		$eventHistoryModel2 = new \models\EventHistoryModel();
		$eventHistoryModel2->setFromDataBaseRow(array(
			'event_id'=>1,
			'summary'=>null,
			'description'=>null,
			'start_at'=>null,
			'end_at'=>null,
			'created_at'=>'2014-01-01 12:10:10',
			'is_deleted'=>null,
			'is_cancelled'=>null,
			'country_id'=>null,
			'timezone'=>null,
			'venue_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'is_virtual'=>null,
			'is_physical'=>null,
			'area_id'=>null,
			'user_account_id'=>null,
			'summary_changed'=>'-1',
			'description_changed'=>'1',
			'start_at_changed'=>'-1',
			'end_at_changed'=>'-1',
			'is_deleted_changed'=>'-1',
			'country_id_changed'=>'-1',
			'timezone_changed'=>'-1',
			'venue_id_changed'=>'-1',
			'url_changed'=>'-1',
			'is_virtual_changed'=>'-1',
			'is_physical_changed'=>'-1',
			'area_id_changed'=>'-1',
			'is_new'=>'-1',
			'custom_fields'=>null,
			'custom_fields_changed'=>null,
		));

		$content2 = new UserWatchesAreaNotifyContent();
		$content2->setWatchedThingTitle("Edinburgh");
		$content2->setHistories(array($eventHistoryModel2));


		$task = new TestSendUserWatchesNotifyTask($app);
		list($newThings, $histories) = $task->testGetNewAndHistoriesForContentsToSend(array($content1, $content2));


		$this->assertEquals(2, count($histories));
		$this->assertEquals($eventHistoryModel2, $histories[0]);
		$this->assertEquals($eventHistoryModel1, $histories[1]);

	}

	public function testGetNewAndHistoriesForContentsToSendSame() {
		global $app;

		// Create 2 seperate objects in memory because that is how it might happen in the app ...
		// but they represent the same piece of data!

		$historydata = array(
			'event_id'=>1,
			'summary'=>null,
			'description'=>null,
			'start_at'=>null,
			'end_at'=>null,
			'created_at'=>'2014-01-01 10:10:10',
			'is_deleted'=>null,
			'is_cancelled'=>null,
			'country_id'=>null,
			'timezone'=>null,
			'venue_id'=>null,
			'url'=>null,
			'ticket_url'=>null,
			'is_virtual'=>null,
			'is_physical'=>null,
			'area_id'=>null,
			'user_account_id'=>null,
			'summary_changed'=>'-1',
			'description_changed'=>'1',
			'start_at_changed'=>'-1',
			'end_at_changed'=>'-1',
			'is_deleted_changed'=>'-1',
			'country_id_changed'=>'-1',
			'timezone_changed'=>'-1',
			'venue_id_changed'=>'-1',
			'url_changed'=>'-1',
			'is_virtual_changed'=>'-1',
			'is_physical_changed'=>'-1',
			'area_id_changed'=>'-1',
			'is_new'=>'-1',
			'custom_fields'=>null,
			'custom_fields_changed'=>null,
		);

		$eventHistoryModel1 = new \models\EventHistoryModel();
		$eventHistoryModel1->setFromDataBaseRow($historydata);

		$content1 = new UserWatchesAreaNotifyContent();
		$content1->setWatchedThingTitle("Edinburgh");
		$content1->setHistories(array($eventHistoryModel1));


		$eventHistoryModel2 = new \models\EventHistoryModel();
		$eventHistoryModel2->setFromDataBaseRow($historydata);

		$content2 = new UserWatchesAreaNotifyContent();
		$content2->setWatchedThingTitle("Edinburgh");
		$content2->setHistories(array($eventHistoryModel2));


		$task = new TestSendUserWatchesNotifyTask($app);
		list($newThings, $histories) = $task->testGetNewAndHistoriesForContentsToSend(array($content1, $content2));


		$this->assertEquals(1, count($histories));
		$eventHistorModel = $histories[0];
		$this->assertEquals(1, $eventHistorModel->getId());
		$this->assertEquals("2014-01-01T10:10:10+00:00", $eventHistorModel->getCreatedAt()->format("c"));

	}

}


