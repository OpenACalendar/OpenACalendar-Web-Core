<?php

namespace import;
use models\ImportResultModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class ImportNotUsHandler extends ImportHandlerBase {
	
	public function getSortOrder() {
		return -1000000;
	}
	
	public function canHandle() {
		global $CONFIG;
		
		$data = parse_url($this->importRun->getRealUrl());
		$host = isset($data['host']) ? $data['host'] : '';
		
		
		$checks = array($this->getDomainMinusPort($CONFIG->webIndexDomain),$this->getDomainMinusPort($CONFIG->webSiteDomain));
		if ($CONFIG->hasSSL) {
			$checks[] = $this->getDomainMinusPort($CONFIG->webSiteDomain);
			$checks[] = $this->getDomainMinusPort($CONFIG->webSiteDomainSSL);
		}
		foreach($checks as $check) {
			if (strpos(strtolower($host), strtolower($check)) !== false) {
				//print "\n\n".$host." AND ".$check."\n\n";
				return true;
			}
		}
		
		return false;
		
	}
	
	public function getDomainMinusPort($in) {
		if (strpos($in, ":")) {
			$bits = explode(":", $in);
			return $bits[0];
		} else {
			return $in;
		}
	}
	
	public function handle() {
		$iurlr = new ImportResultModel();
		$iurlr->setIsSuccess(false);
		$iurlr->setMessage("You can't import from the same site!");
		return $iurlr;	
	}

	
	
}

