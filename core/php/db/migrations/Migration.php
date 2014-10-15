<?php

namespace db\migrations;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class Migration {
	protected  $id;
	protected $sql;
	protected $applied = false;


	public function __construct($id=null, $sql=null) {
		$this->id = $id;
		$this->sql = $sql;
	}
	
	public function getId() { return $this->id; }
	public function getApplied() { return $this->applied; }
	public function setIsApplied() { $this->applied = true; }

	public  function performMigration(\PDO $db, \TimeSource $timeSource, \Config $config) {
		foreach(explode(";", $this->sql) as $line) {
			if (trim($line)) {
				$db->query($line.';');
			}
		}
	}
	
	public function getIdAsUnixTimeStamp() {
		$year = substr($this->id, 0, 4);
		$month = substr($this->id, 4, 2);
		$day = substr($this->id, 6, 2);
		$hour = substr($this->id, 8, 2);
		$min = substr($this->id, 10, 2);
		$sec = substr($this->id, 12, 2);
		return mktime($hour,$min,$sec,$month,$day,$year);
	}
}


