<?php

namespace repositories\builders\filterparams;

use repositories\builders\ImportedEventRepositoryBuilder;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportedEventFilterParams {

	function __construct(Application $app, ImportedEventRepositoryBuilder $erb = null) {
		if ($erb) {
			$this->importedEventRepositoryBuilder = $erb;
		} else {
			$this->importedEventRepositoryBuilder = new ImportedEventRepositoryBuilder();
			$this->importedEventRepositoryBuilder->setLimit(100);
		}
	}

	
	protected $importedEventRepositoryBuilder;
	
	public function getImportedEventRepositoryBuilder() {
		return $this->importedEventRepositoryBuilder;
	}

	// ############################### optional controls; turn on and off
	
	protected $hasDateControls = true;
	
	public function getDateControls() {
		return $this->hasDateControls;
	}
	
	public function setHasDateControls($hasDateControls) {
		$this->hasDateControls = $hasDateControls;
	}

	// ############################### params
	
	protected $fromNow = true;
	protected $from;
	protected $includeSpecifiedUserAttending = true;
	protected $includeSpecifiedUserWatching = true;


	public function set($data) {
		if (isset($data['importedEventListFilterDataSubmitted'])) {
		
			// From
			if ($this->hasDateControls) {
				$fromNow = isset($data['fromNow']) ? $data['fromNow'] : 0;
				if (!$fromNow) {
					$this->fromNow = false;
					$from = isset($data['from']) ? strtolower(trim($data['from'])) : null;
					if ($from) {
						try {
							$fromDT = new \DateTime($from, new \DateTimeZone('UTC'));
							$fromDT->setTime(0, 0, 0);
							$this->from = $fromDT->format('j F Y');							
						} catch (\Exception $e) {
							// assume it's parse exception, ignore.
						}
					}
				}
			}
			
			
		}
		
		// apply to search
		if ($this->fromNow) {
			$this->importedEventRepositoryBuilder->setAfterNow();
		} else if ($this->from) {
			$this->importedEventRepositoryBuilder->setAfter($fromDT);
		}

	}
	
	public function getFrom() {
		return $this->from;
	}
	public function getFromNow() {
		return $this->fromNow;
	}

}


