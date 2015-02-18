<?php

namespace tasks;


use models\TagHistoryModel;
use repositories\TagHistoryRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateTagHistoryChangeFlagsTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateTagHistoryChangeFlagsTask';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateTagHistoryChangeFlagsAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateTagHistoryChangeFlagsAutomaticUpdateInterval;
	}

	protected function run() {


		$tagHistoryRepo = new TagHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM tag_history");
		$stat->execute();
		$count = 0;
		while($data = $stat->fetch()) {
			$tagHistory = new TagHistoryModel();
			$tagHistory->setFromDataBaseRow($data);

			$tagHistoryRepo->ensureChangedFlagsAreSet($tagHistory);
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
