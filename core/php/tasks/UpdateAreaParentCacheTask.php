<?php

namespace tasks;

use repositories\builders\AreaRepositoryBuilder;
use repositories\AreaRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateAreaParentCacheTask extends \BaseTask {


	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateAreaParentCache';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateAreaParentCacheAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateAreaParentCacheAutomaticUpdateInterval;
	}

	protected function run() {
		$areaRepository = new AreaRepository();

		$arb = new AreaRepositoryBuilder();
		$arb->setLimit(1000000);
		$arb->setCacheNeedsBuildingOnly(true);

		$count = 0;
		foreach($arb->fetchAll() as $area) {
			$areaRepository->buildCacheAreaHasParent($area);
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

