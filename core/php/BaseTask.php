<?php

use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class BaseTask {


	/** @var Application */
	protected $app;

	protected $logVerbosePrint = false;

	function __construct($app)
	{
		$this->app = $app;
	}

	abstract public function getExtensionId();
	abstract public function getTaskId();

	/** @return boolean */
	public function getShouldRunAutomaticallyNow() {
		return false;
	}

	/** @return boolean */
	public function getCanRunManuallyNow() {
		return true;
	}

	/** @return Array Ready to be made into JSON */
	abstract protected function run();

	public function getLastRunEndedAgoInSeconds() {
		$stat = $this->app['db']->prepare("SELECT ended_at FROM task_log ".
			"WHERE extension_id=:extension_id AND task_id=:task_id AND ended_at IS NOT NULL ".
			"ORDER BY ended_at DESC LIMIT 1");
		$stat->execute(array(
			'extension_id'=>$this->getExtensionId(),
			'task_id'=>$this->getTaskId(),
		));
		if ($stat->rowCount() > 0) {
			$data = $stat->fetch();

			$endedAt = new \DateTime($data['ended_at'], new \DateTimeZone('UTC'));

			return $this->app['timesource']->getDateTime()->getTimestamp() - $endedAt->getTimestamp();

		} else {
			return 1000000;
		}

	}

	public function hasRunToday() {
		$start = \TimeSource::getDateTime();
		$start->setTime(0, 0, 0);
		$stat = $this->app['db']->prepare("SELECT ended_at FROM task_log ".
			"WHERE extension_id=:extension_id AND task_id=:task_id AND started_at > :started_at ".
			"ORDER BY ended_at DESC LIMIT 1");
		$stat->execute(array(
			'extension_id'=>$this->getExtensionId(),
			'task_id'=>$this->getTaskId(),
			'started_at'=>$start->format("Y-m-d H:i:s")
		));
		return $stat->rowCount() > 0;
	}


	public function runAutomaticallyNowIfShould($logVerbosePrint = false) {
		if ($this->getShouldRunAutomaticallyNow()) {

			$this->logVerbosePrint = $logVerbosePrint;

			$startedAt = $this->app['timesource']->getFormattedForDataBase();
			$this->logVerbose("Starting ".$startedAt);

			$stat = $this->app['db']->prepare("INSERT INTO task_log (extension_id, task_id, started_at) VALUES (:extension_id, :task_id, :started_at)");
			$stat->execute(array(
				'extension_id'=>$this->getExtensionId(),
				'task_id'=>$this->getTaskId(),
				'started_at'=>$startedAt,
			));

			$exceptionData = null;
			$data = null;

			try {
				$data = $this->run();
			} catch(Exception $e) {
				$exceptionData = array(
					'message'=>$e->getMessage(),
					'code'=>$e->getCode(),
					'file'=>$e->getFile(),
					'line'=>$e->getLine(),
				);
				$this->logVerbose("EXCEPTION ".$e->getMessage());
			}

			$endedAt = $this->app['timesource']->getFormattedForDataBase();
			$this->logVerbose("Finished ".$endedAt);

			$stat = $this->app['db']->prepare("UPDATE task_log SET ended_at=:ended_at, result_data=:result_data, exception_data=:exception_data ".
				"WHERE extension_id=:extension_id AND task_id=:task_id AND started_at=:started_at");
			$stat->execute(array(
				'extension_id'=>$this->getExtensionId(),
				'task_id'=>$this->getTaskId(),
				'started_at'=>$startedAt,
				'ended_at'=>$endedAt,
				'result_data'=>$data ? json_encode($data) : null,
				'exception_data'=>$exceptionData ? json_encode($exceptionData) : null,
			));

			// The Exception may have left the DB or other resources in a bad state. Throw exception upwards so calling script knows.
			if ($exceptionData) {
				throw new \Exception("Exception Running Task!");
			}

		}

	}


	public function runManuallyNowIfShould($logVerbosePrint = false) {
		if ($this->getCanRunManuallyNow()) {

			$this->logVerbosePrint = $logVerbosePrint;

			$startedAt = $this->app['timesource']->getFormattedForDataBase();
			$this->logVerbose("Starting ".$startedAt);

			$stat = $this->app['db']->prepare("INSERT INTO task_log (extension_id, task_id, started_at) VALUES (:extension_id, :task_id, :started_at)");
			$stat->execute(array(
				'extension_id'=>$this->getExtensionId(),
				'task_id'=>$this->getTaskId(),
				'started_at'=>$startedAt,
			));

			$exceptionData = null;
			$data = null;

			try {
				$data = $this->run();
			} catch(Exception $e) {
				$exceptionData = array(
					'message'=>$e->getMessage(),
					'code'=>$e->getCode(),
					'file'=>$e->getFile(),
					'line'=>$e->getLine(),
				);
				$this->logVerbose("EXCEPTION ".$e->getMessage());
			}

			$endedAt = $this->app['timesource']->getFormattedForDataBase();
			$this->logVerbose("Finished ".$endedAt);

			$stat = $this->app['db']->prepare("UPDATE task_log SET ended_at=:ended_at, result_data=:result_data, exception_data=:exception_data ".
				"WHERE extension_id=:extension_id AND task_id=:task_id AND started_at=:started_at");
			$stat->execute(array(
				'extension_id'=>$this->getExtensionId(),
				'task_id'=>$this->getTaskId(),
				'started_at'=>$startedAt,
				'ended_at'=>$endedAt,
				'result_data'=>$data ? json_encode($data) : null,
				'exception_data'=>$exceptionData ? json_encode($exceptionData) : null,
			));

		}

	}


	protected function logVerbose($message) {
		if ($this->logVerbosePrint) {
			print "        ".$message."\n";
		}
	}

	public function getResultDataAsString(\models\TaskLogModel $taskLogModel) {
		return '';
	}

	public function getExceptionDataAsString(\models\TaskLogModel $taskLogModel) {
		$exception = $taskLogModel->getExceptionData();
		return $exception->message . " of code " . $exception->code." from line ".$exception->line ." from file ".$exception->file;
	}

	public function getSeriesReports() {
		return array();
	}

	public function getValueReports() {
		return array();
	}


}
