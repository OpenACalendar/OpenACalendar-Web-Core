<?php

namespace org\openacalendar\curatedlists\tasks;

use org\openacalendar\curatedlists\models\CuratedListHistoryModel;
use org\openacalendar\curatedlists\repositories\CuratedListHistoryRepository;


/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class UpdateCuratedListHistoryChangeFlagsTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar.curatedlists';
	}

	public function getTaskId()
	{
		return 'UpdateHistoryChangeFlagsTask';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->getLastRunEndedAgoInSeconds() > 30*60; // TODO $config
	}

	protected function run() {


		$curatedListHistoryRepo = new CuratedListHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM curated_list_history");
		$stat->execute();
		$count = 0;
		while($data = $stat->fetch()) {
			$curatedListHistory = new CuratedListHistoryModel();
			$curatedListHistory->setFromDataBaseRow($data);

			$curatedListHistoryRepo->ensureChangedFlagsAreSet($curatedListHistory);
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
