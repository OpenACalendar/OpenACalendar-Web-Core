<?php

namespace import;
use models\ImportURLModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
abstract class ImportURLHandlerBase {

	
	protected $limitToSaveOnEachRun = 20;
	
	public function setLimitToSaveOnEachRun($limit) { $this->limitToSaveOnEachRun = $limit; }
	
	
	
	/** @var ImportURLRun **/
	protected $importURLRun;
	
	public function __construct(ImportURLRun $importURLRun) {
		$this->importURLRun = $importURLRun;
	}
	
	public abstract function canHandle();
	
	public abstract function handle();
		
}

