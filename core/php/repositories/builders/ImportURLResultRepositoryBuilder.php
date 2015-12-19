<?php

namespace repositories\builders;

use models\ImportURLModel;
use models\ImportURLResultModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLResultRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var ImportURLModel **/
	protected $importURL;
	
	public function setImportURL(ImportURLModel $importURL) {
		$this->importURL = $importURL;
		return $this;
	}
	
	protected $limit = 100;


	protected function build() {

		if ($this->importURL) {
			$this->where[] =  " import_url_result.import_url_id = :import_url_id ";
			$this->params['import_url_id'] = $this->importURL->getId();
		}
		
	}
	

	protected function buildStat() {
				global $DB;
		
		
		$sql = "SELECT import_url_result.* FROM import_url_result ".
				" WHERE ".implode(" AND ", $this->where).
				" ORDER BY import_url_result.created_at DESC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $DB->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	

	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$importURLResult = new ImportURLResultModel();
			$importURLResult->setFromDataBaseRow($data);
			$results[] = $importURLResult;
		}
		return $results;
		
	}

}

