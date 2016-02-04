<?php

namespace tasks;


use models\AreaHistoryModel;
use repositories\AreaHistoryRepository;
/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateAreaHistoryChangeFlagsTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateAreaHistoryChangeFlagsTask';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateAreaHistoryChangeFlagsAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateAreaHistoryChangeFlagsAutomaticUpdateInterval;
	}

	protected function run() {

		$areaHistoryRepo = new AreaHistoryRepository($this->app);
		$stat = $this->app['db']->prepare("SELECT * FROM area_history");
		$stat->execute();
		$count = 0;
		while($data = $stat->fetch()) {
			$areaHistory = new AreaHistoryModel();
			$areaHistory->setFromDataBaseRow($data);

			$areaHistoryRepo->ensureChangedFlagsAreSet($areaHistory);
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
