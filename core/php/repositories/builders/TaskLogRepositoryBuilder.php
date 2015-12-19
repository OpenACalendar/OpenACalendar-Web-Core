<?php

namespace repositories\builders;


use models\TaskLogModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TaskLogRepositoryBuilder  extends BaseRepositoryBuilder {


	/** @var  TaskLogModel */
	protected $task;

	/**
	 * @param \BaseTask $task
	 */
	public function setTask(\BaseTask $task)
	{
		$this->task = $task;
	}

	protected function build() {

		if ($this->task) {
			$this->where[] = " task_log.extension_id = :extension_id AND task_log.task_id = :task_id ";
			$this->params['task_id'] = $this->task->getTaskId();
			$this->params['extension_id'] = $this->task->getExtensionId();
		}

	}

	protected function buildStat() {
				global $DB;



		$sql = "SELECT task_log.* FROM task_log ".
				implode(" ",$this->joins).
				($this->where?" WHERE ".implode(" AND ", $this->where):"").
				" ORDER BY task_log.started_at DESC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");

		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}


	public function fetchAll() {

		$this->buildStart();
		$this->build();
		$this->buildStat();



		$results = array();
		while($data = $this->stat->fetch()) {
			$task = new TaskLogModel();
			$task->setFromDataBaseRow($data);
			$results[] = $task;
		}
		return $results;

	}

}

