<?php


namespace tests\eventcustomfields;
use BaseAppWithDBTest;
use customfieldtypes\event\TextSingleLineEventCustomFieldType;
use models\EventCustomFieldDefinitionModel;
use models\EventEditMetaDataModel;
use models\EventHistoryModel;
use models\EventModel;
use models\SiteModel;
use models\UserAccountModel;
use repositories\builders\EventHistoryRepositoryBuilder;
use repositories\EventCustomFieldDefinitionRepository;
use repositories\EventHistoryRepository;
use repositories\EventRepository;
use repositories\SiteRepository;
use repositories\UserAccountRepository;
use TimeSource;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventTest extends BaseAppWithDBTest
{


	public function testAddCustomFieldThenCreateEventThenEditToAddContent() {

		TimeSource::mock(2014,5,1,7,0,0);

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

		$customFieldDefinition1 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition1->setSiteId($site->getId());
		$customFieldDefinition1->setExtensionId('org.openacalendar');
		$customFieldDefinition1->setType('TextSingleLine');
		$customFieldDefinition1->setKey('cats');
		$customFieldDefinition1->setLabel('cats');

		$customFieldDefinition2 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition2->setSiteId($site->getId());
		$customFieldDefinition2->setExtensionId('org.openacalendar');
		$customFieldDefinition2->setType('TextSingleLine');
		$customFieldDefinition2->setKey('dogs');
		$customFieldDefinition2->setLabel('dogs');

		$ecfRepo = new EventCustomFieldDefinitionRepository();
		$ecfRepo->create($customFieldDefinition1, $user);
		$ecfRepo->create($customFieldDefinition2, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		// CREATE

		TimeSource::mock(2014,5,1,7,1,0);

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$event = $eventRepository->loadByID($event->getId());

		$this->assertEquals(false, $event->hasCustomField($customFieldDefinition1));
		$this->assertEquals(false, $event->hasCustomField($customFieldDefinition2));
		$this->assertFalse($event->getIsDeleted());


		// EDIT FIELD 1

		TimeSource::mock(2014,5,1,7,2,0);


		$event = $eventRepository->loadByID($event->getId());

		$event->setCustomField($customFieldDefinition1, "CATS");

		$eemd = new EventEditMetaDataModel();
		$eemd->setUserAccount($user);
		$eventRepository->editWithMetaData($event, $eemd);


		$event = $eventRepository->loadByID($event->getId());


		$this->assertEquals(true, $event->hasCustomField($customFieldDefinition1));
		$this->assertEquals(false, $event->hasCustomField($customFieldDefinition2));
		$this->assertFalse($event->getIsDeleted());

		// EDIT FIELD 2

		TimeSource::mock(2014,5,1,7,3,0);


		$event = $eventRepository->loadByID($event->getId());

		$event->setCustomField($customFieldDefinition2, "WOOF");

		$eemd = new EventEditMetaDataModel();
		$eemd->setUserAccount($user);
		$eventRepository->editWithMetaData($event, $eemd);


		$event = $eventRepository->loadByID($event->getId());


		$this->assertEquals(true, $event->hasCustomField($customFieldDefinition1));
		$this->assertEquals(true, $event->hasCustomField($customFieldDefinition2));
		$this->assertFalse($event->getIsDeleted());

		// DELETE
		// this is an edit that should mark custom fields change unknown.
		TimeSource::mock(2014,5,1,7,4,0);

		$event = $eventRepository->loadByID($event->getId());

		$eemd = new EventEditMetaDataModel();
		$eemd->setUserAccount($user);
		$eventRepository->deleteWithMetaData($event, $eemd );


		$event = $eventRepository->loadByID($event->getId());

		$this->assertTrue($event->getIsDeleted());

		// LET's CHECK HISTORY

		$eventHistoryRepo = new EventHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM event_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$eventHistory = new EventHistoryModel();
			$eventHistory->setFromDataBaseRow($data);
			$eventHistoryRepo->ensureChangedFlagsAreSet($eventHistory);
		}

		$eventHistoryRepoBuilder = new EventHistoryRepositoryBuilder();
		$eventHistoryRepoBuilder->setEvent($event);
		$histories = $eventHistoryRepoBuilder->fetchAll();

		$this->assertEquals(4, count($histories));


		$historyCreate = $histories[0];
		$this->assertTrue($historyCreate->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertFalse($historyCreate->getCustomFieldChanged($customFieldDefinition1));
		$this->assertFalse($historyCreate->getCustomFieldChanged($customFieldDefinition2));

		$historyEditField1 = $histories[1];
		$this->assertTrue($historyEditField1->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertTrue($historyEditField1->getCustomFieldChanged($customFieldDefinition1));
		$this->assertFalse($historyEditField1->getCustomFieldChanged($customFieldDefinition2));
		$this->assertFalse($historyEditField1->getIsDeletedChanged());

		$historyEditField2 = $histories[2];
		$this->assertTrue($historyEditField2->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertFalse($historyEditField2->getCustomFieldChanged($customFieldDefinition1));
		$this->assertTrue($historyEditField2->getCustomFieldChanged($customFieldDefinition2));
		$this->assertFalse($historyEditField2->getIsDeletedChanged());


		$historyDelete = $histories[3];
		$this->assertFalse($historyDelete->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertFalse($historyDelete->getCustomFieldChanged($customFieldDefinition1));
		$this->assertFalse($historyDelete->getCustomFieldChanged($customFieldDefinition2));
		$this->assertTrue($historyDelete->getIsDeletedChanged());


	}


	public function testCreateEventThenAddCustomFieldThenAddContent() {

		TimeSource::mock(2014,5,1,7,0,0);

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

		$ecfRepo = new EventCustomFieldDefinitionRepository();

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");

		// CREATE

		TimeSource::mock(2014,5,1,7,1,0);

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$event = $eventRepository->loadByID($event->getId());


		$this->assertFalse($event->getIsDeleted());

		// ADD CUSTOM FIELD 1

		$customFieldDefinition1 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition1->setSiteId($site->getId());
		$customFieldDefinition1->setExtensionId('org.openacalendar');
		$customFieldDefinition1->setType('TextSingleLine');
		$customFieldDefinition1->setKey('cats');
		$customFieldDefinition1->setLabel('cats');

		$ecfRepo->create($customFieldDefinition1, $user);

		// EDIT FIELD 1

		TimeSource::mock(2014,5,1,7,2,0);


		$event = $eventRepository->loadByID($event->getId());

		$event->setCustomField($customFieldDefinition1, "CATS");

		$eemd = new EventEditMetaDataModel();
		$eemd->setUserAccount($user);
		$eventRepository->editWithMetaData($event, $eemd);


		$event = $eventRepository->loadByID($event->getId());


		$this->assertEquals(true, $event->hasCustomField($customFieldDefinition1));
		$this->assertFalse($event->getIsDeleted());

		// ADD CUSTOM FIELD 2

		$customFieldDefinition2 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition2->setSiteId($site->getId());
		$customFieldDefinition2->setExtensionId('org.openacalendar');
		$customFieldDefinition2->setType('TextSingleLine');
		$customFieldDefinition2->setKey('dogs');
		$customFieldDefinition2->setLabel('dogs');

		$ecfRepo->create($customFieldDefinition2, $user);

		// EDIT FIELD 2

		TimeSource::mock(2014,5,1,7,3,0);


		$event = $eventRepository->loadByID($event->getId());

		$event->setCustomField($customFieldDefinition2, "WOOF");

		$eemd = new EventEditMetaDataModel();
		$eemd->setUserAccount($user);
		$eventRepository->editWithMetaData($event, $eemd);


		$event = $eventRepository->loadByID($event->getId());


		$this->assertEquals(true, $event->hasCustomField($customFieldDefinition1));
		$this->assertEquals(true, $event->hasCustomField($customFieldDefinition2));
		$this->assertFalse($event->getIsDeleted());

		// DELETE
		// this is an edit that should mark custom fields change unknown.
		TimeSource::mock(2014,5,1,7,4,0);

		$event = $eventRepository->loadByID($event->getId());

		$eemd = new EventEditMetaDataModel();
		$eemd->setUserAccount($user);
		$eventRepository->deleteWithMetaData($event, $eemd );


		$event = $eventRepository->loadByID($event->getId());

		$this->assertTrue($event->getIsDeleted());

		// LET's CHECK HISTORY

		$eventHistoryRepo = new EventHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM event_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$eventHistory = new EventHistoryModel();
			$eventHistory->setFromDataBaseRow($data);
			$eventHistoryRepo->ensureChangedFlagsAreSet($eventHistory);
		}

		$eventHistoryRepoBuilder = new EventHistoryRepositoryBuilder();
		$eventHistoryRepoBuilder->setEvent($event);
		$histories = $eventHistoryRepoBuilder->fetchAll();

		$this->assertEquals(4, count($histories));


		$historyCreate = $histories[0];
		// Check isAnyChangeFlagsUnknown() to test https://github.com/OpenACalendar/OpenACalendar-Web-Core/commit/b2beb50c2c95175db74abe5fef0903ce202f91fa
		$this->assertFalse($historyCreate->isAnyChangeFlagsUnknown());
		$this->assertTrue($historyCreate->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertFalse($historyCreate->getCustomFieldChanged($customFieldDefinition1));
		$this->assertFalse($historyCreate->getCustomFieldChanged($customFieldDefinition2));

		$historyEditField1 = $histories[1];
		$this->assertFalse($historyEditField1->isAnyChangeFlagsUnknown());
		$this->assertTrue($historyEditField1->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertTrue($historyEditField1->getCustomFieldChanged($customFieldDefinition1));
		$this->assertFalse($historyEditField1->getCustomFieldChanged($customFieldDefinition2));
		$this->assertFalse($historyEditField1->getIsDeletedChanged());

		$historyEditField2 = $histories[2];
		$this->assertFalse($historyEditField2->isAnyChangeFlagsUnknown());
		$this->assertTrue($historyEditField2->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertFalse($historyEditField2->getCustomFieldChanged($customFieldDefinition1));
		$this->assertTrue($historyEditField2->getCustomFieldChanged($customFieldDefinition2));
		$this->assertFalse($historyEditField2->getIsDeletedChanged());


		$historyDelete = $histories[3];
		$this->assertFalse($historyDelete->isAnyChangeFlagsUnknown());
		$this->assertFalse($historyDelete->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertFalse($historyDelete->getCustomFieldChanged($customFieldDefinition1));
		$this->assertFalse($historyDelete->getCustomFieldChanged($customFieldDefinition2));
		$this->assertTrue($historyDelete->getIsDeletedChanged());


	}




	public function testAddCustomFieldThenCreateEventWithContent() {

		TimeSource::mock(2014,5,1,7,0,0);

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

		$customFieldDefinition1 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition1->setSiteId($site->getId());
		$customFieldDefinition1->setExtensionId('org.openacalendar');
		$customFieldDefinition1->setType('TextSingleLine');
		$customFieldDefinition1->setKey('cats');
		$customFieldDefinition1->setLabel('cats');

		$customFieldDefinition2 = new EventCustomFieldDefinitionModel();
		$customFieldDefinition2->setSiteId($site->getId());
		$customFieldDefinition2->setExtensionId('org.openacalendar');
		$customFieldDefinition2->setType('TextSingleLine');
		$customFieldDefinition2->setKey('dogs');
		$customFieldDefinition2->setLabel('dogs');

		$ecfRepo = new EventCustomFieldDefinitionRepository();
		$ecfRepo->create($customFieldDefinition1, $user);
		$ecfRepo->create($customFieldDefinition2, $user);

		$event = new EventModel();
		$event->setSummary("test");
		$event->setDescription("test test");
		$event->setStartAt(getUTCDateTime(2014,5,10,19,0,0));
		$event->setEndAt(getUTCDateTime(2014,5,10,21,0,0));
		$event->setUrl("http://www.info.com");
		$event->setTicketUrl("http://www.tickets.com");
		$event->setCustomField($customFieldDefinition1, "CATS");

		// CREATE WITH

		TimeSource::mock(2014,5,1,7,1,0);

		$eventRepository = new EventRepository();
		$eventRepository->create($event, $site, $user);

		$event = $eventRepository->loadByID($event->getId());

		$this->assertEquals(true, $event->hasCustomField($customFieldDefinition1));
		$this->assertEquals(false, $event->hasCustomField($customFieldDefinition2));
		$this->assertFalse($event->getIsDeleted());



		// LET's CHECK HISTORY

		$eventHistoryRepo = new EventHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM event_history");
		$stat->execute();
		while($data = $stat->fetch()) {
			$eventHistory = new EventHistoryModel();
			$eventHistory->setFromDataBaseRow($data);
			$eventHistoryRepo->ensureChangedFlagsAreSet($eventHistory);
		}

		$eventHistoryRepoBuilder = new EventHistoryRepositoryBuilder();
		$eventHistoryRepoBuilder->setEvent($event);
		$histories = $eventHistoryRepoBuilder->fetchAll();

		$this->assertEquals(1, count($histories));


		$historyCreate = $histories[0];
		$this->assertTrue($historyCreate->getCustomFieldChangedKnown($customFieldDefinition1));
		$this->assertTrue($historyCreate->getCustomFieldChanged($customFieldDefinition1));
		$this->assertFalse($historyCreate->getCustomFieldChanged($customFieldDefinition2));



	}

}


