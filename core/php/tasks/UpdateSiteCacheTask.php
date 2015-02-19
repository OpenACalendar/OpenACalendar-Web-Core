<?php

namespace tasks;


use repositories\builders\SiteRepositoryBuilder;
use repositories\builders\CountryRepositoryBuilder;
use repositories\SiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UpdateSiteCacheTask extends \BaseTask {

	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'UpdateSiteCache';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskUpdateSiteCacheAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskUpdateSiteCacheAutomaticUpdateInterval;
	}

	protected function run() {

		$siteRepository = new SiteRepository();

		$siteRepositoryBuilder = new SiteRepositoryBuilder();
		$count = 0;
		foreach($siteRepositoryBuilder->fetchAll() as $site) {

			$crb = new CountryRepositoryBuilder();
			$crb->setSiteIn($site);
			$countries = $crb->fetchAll();

			$timezones = array();
			foreach($countries as $country) {
				foreach(explode(",", $country->getTimezones()) as $timeZone) {
					$timezones[] = $timeZone;
				}
			}

			$site->setCachedTimezonesAsList($timezones);
			$site->setCachedIsMultipleCountries(count($countries) > 1);

			$siteRepository->editCached($site);

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

