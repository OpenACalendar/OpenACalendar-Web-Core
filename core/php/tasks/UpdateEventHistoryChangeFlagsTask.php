<?php

namespace tasks;


use models\EventHistoryModel;
use repositories\EventHistoryRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateEventHistoryChangeFlagsTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateEventHistoryChangeFlagsTask';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateEventHistoryChangeFlagsAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateEventHistoryChangeFlagsAutomaticUpdateInterval;
	}

	protected function run() {


		$eventHistoryRepo = new EventHistoryRepository($this->app);
		$stat = $this->app['db']->prepare("SELECT * FROM event_history");
		$stat->execute();
		$count = 0;
		while($data = $stat->fetch()) {
			$eventHistory = new EventHistoryModel();
			$eventHistory->setFromDataBaseRow($data);

			$eventHistoryRepo->ensureChangedFlagsAreSet($eventHistory);
			++$count;
		}

		return array('result'=>'ok','count'=>$count);
	}

	public function getResultDataAsString(\models\TaskLogModel $taskLogModel) {
		if ($taskLogModel->getIsResultDataHaveKey("result") && $taskLogModel->getResultDataValue("result") == "ok") {
			return "Ok";
		} else {
			return "Fail";
		}

	}




}
