<?php


namespace repositories;

use models\UserAccountModel;
use models\EventModel;
use models\UserAtEventModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAtEventRepository {


    /** @var Application */
    private  $app;


    function __construct(Application $app)
    {
        $this->app = $app;
    }

	
	/** @return UserAtEventModel **/
	public function loadByUserAndEvent(UserAccountModel $user, EventModel $event) {
		return $this->loadByUserIDAndEvent($user->getId(), $event);
	}

	/** @return UserAtEventModel **/
	public function loadByUserIDAndEvent($userId, EventModel $event) {

		$stat = $this->app['db']->prepare("SELECT user_at_event_information.* FROM user_at_event_information WHERE user_account_id =:user_account_id AND event_id=:event_id");
		$stat->execute(array( 'user_account_id'=>$userId, 'event_id'=>$event->getId() ));
		if ($stat->rowCount() > 0) {
			$uaem = new UserAtEventModel();
			$uaem->setFromDataBaseRow($stat->fetch());
			return $uaem;
		}
	}

	/** @return UserAtEventModel **/
	public function loadByUserAndEventOrInstanciate(UserAccountModel $user, EventModel $event) {
		return $this->loadByUserIDAndEventOrInstanciate($user->getId(), $event);
	}

	/** @return UserAtEventModel **/
	public function loadByUserIDAndEventOrInstanciate($userId, EventModel $event) {
		$uaem = $this->loadByUserIDAndEvent($userId, $event);
		if (!$uaem) {
			$uaem = new UserAtEventModel();
			$uaem->setEventId($event->getId());
			$uaem->setUserAccountId($userId);
		}
		return $uaem;
	}
	
	/** This function could create or edit **/
	public function save(UserAtEventModel $userAtEvent) {

		$stat = $this->app['db']->prepare("SELECT user_at_event_information.* FROM user_at_event_information WHERE user_account_id =:user_account_id AND event_id=:event_id");
		$stat->execute(array( 'user_account_id'=>$userAtEvent->getUserAccountId(), 'event_id'=>$userAtEvent->getEventId() ));
		if ($stat->rowCount() == 0) {
			$this->create($userAtEvent);
		} else {
			$this->edit($userAtEvent);
		}
	}
	
	public function create(UserAtEventModel $userAtEvent) {

		$stat = $this->app['db']->prepare("INSERT INTO user_at_event_information (user_account_id,event_id,is_plan_attending,is_plan_maybe_attending,is_plan_not_attending,is_plan_public,created_at) ".
				"VALUES (:user_account_id,:event_id,:is_plan_attending,:is_plan_maybe_attending,:is_plan_not_attending,:is_plan_public,:created_at)");
		$stat->execute(array(
				'user_account_id'=>$userAtEvent->getUserAccountId(),
				'event_id'=>$userAtEvent->getEventId(),
				'is_plan_attending'=>$userAtEvent->getIsPlanAttending()?1:0,
				'is_plan_maybe_attending'=>$userAtEvent->getIsPlanMaybeAttending()?1:0,
				'is_plan_not_attending'=>$userAtEvent->getIsPlanNotAttending()?1:0,
				'is_plan_public'=>$userAtEvent->getIsPlanPublic()?1:0,
				'created_at'=>  $this->app['timesource']->getFormattedForDataBase(),
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserAtEventSaved', array('user_account_id'=>$userAtEvent->getUserAccountId(),'event_id'=>$userAtEvent->getEventId()));
	}

	public function edit(UserAtEventModel $userAtEvent) {

		$stat = $this->app['db']->prepare("UPDATE user_at_event_information SET ".
				" is_plan_attending=:is_plan_attending, is_plan_maybe_attending=:is_plan_maybe_attending, is_plan_public=:is_plan_public, is_plan_not_attending=:is_plan_not_attending ".
				" WHERE user_account_id=:user_account_id AND event_id = :event_id");
		$stat->execute(array(
				'user_account_id'=>$userAtEvent->getUserAccountId(),
				'event_id'=>$userAtEvent->getEventId(),
				'is_plan_attending'=>$userAtEvent->getIsPlanAttending()?1:0,
				'is_plan_maybe_attending'=>$userAtEvent->getIsPlanMaybeAttending()?1:0,
                'is_plan_not_attending'=>$userAtEvent->getIsPlanNotAttending()?1:0,
				'is_plan_public'=>$userAtEvent->getIsPlanPublic()?1:0,
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserAtEventSaved', array('user_account_id'=>$userAtEvent->getUserAccountId(),'event_id'=>$userAtEvent->getEventId()));
	}
	
}

