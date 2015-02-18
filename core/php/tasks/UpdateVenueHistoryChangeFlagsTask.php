<?php

namespace tasks;


use models\VenueHistoryModel;
use repositories\VenueHistoryRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateVenueHistoryChangeFlagsTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateVenueHistoryChangeFlagsTask';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateHistoryChangeFlagsTaskAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateHistoryChangeFlagsTaskAutomaticUpdateInterval;
	}

	protected function run() {

		$venueHistoryRepo = new VenueHistoryRepository();
		$stat = $this->app['db']->prepare("SELECT * FROM venue_history");
		$stat->execute();
		$count = 0;
		while($data = $stat->fetch()) {
			$venueHistory = new VenueHistoryModel();
			$venueHistory->setFromDataBaseRow($data);

			$venueHistoryRepo->ensureChangedFlagsAreSet($venueHistory);
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
