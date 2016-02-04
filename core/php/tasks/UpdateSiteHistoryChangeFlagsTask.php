<?php

namespace tasks;


use models\SiteHistoryModel;
use repositories\SiteHistoryRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateSiteHistoryChangeFlagsTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateSiteHistoryChangeFlagsTask';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateSiteHistoryChangeFlagsAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateSiteHistoryChangeFlagsAutomaticUpdateInterval;
	}

	protected function run() {

		$siteHistoryRepo = new SiteHistoryRepository($this->app);
		$stat = $this->app['db']->prepare("SELECT * FROM site_history");
		$stat->execute();
		$count = 0;
		while($data = $stat->fetch()) {
			$siteHistory = new SiteHistoryModel();
			$siteHistory->setFromDataBaseRow($data);

			$siteHistoryRepo->ensureChangedFlagsAreSet($siteHistory);
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
