<?php

namespace import;

use models\ImportModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class ImportHandlerBase {

	
	protected $limitToSaveOnEachRun = 20;
	
	public function setLimitToSaveOnEachRun($limit) { $this->limitToSaveOnEachRun = $limit; }
	
	
	
	/** @var ImportRun **/
	protected $importRun;
	
	public function setImportRun(ImportRun $importRun) {
		$this->importRun = $importRun;
	}
	
	public abstract function canHandle();
	
	public abstract function handle();
		
	
	/**
	 * 
	 * 
	 * 
	 * @return boolean
	 */
	public function isStopAfterHandling() { 
		return true;
	}
	
	
	/**
	 * 
	 * Handlers are sorted into order before running.
	 * 
	 * Lower values are run first.
	 * 
	 * @return int
	 */
	public function getSortOrder() { 
		return 0;
	}
	
}

