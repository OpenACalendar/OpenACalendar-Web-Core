<?php

namespace tasks;


use models\GroupHistoryModel;
use repositories\GroupHistoryRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateGroupHistoryChangeFlagsTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateGroupHistoryChangeFlagsTask';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateGroupHistoryChangeFlagsAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateGroupHistoryChangeFlagsAutomaticUpdateInterval;
	}

	protected function run() {

		$groupHistoryRepo = new GroupHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM group_history");
		$stat->execute();
		$count = 0;
		while($data = $stat->fetch()) {
			$groupHistory = new GroupHistoryModel();
			$groupHistory->setFromDataBaseRow($data);

			$groupHistoryRepo->ensureChangedFlagsAreSet($groupHistory);
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
