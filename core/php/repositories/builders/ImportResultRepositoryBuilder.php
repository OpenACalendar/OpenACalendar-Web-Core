<?php

namespace repositories\builders;

use models\ImportModel;
use models\ImportResultModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportResultRepositoryBuilder  extends BaseRepositoryBuilder {
	

	/** @var ImportModel **/
	protected $importURL;
	
	public function setImport(ImportModel $import) {
		$this->import = $import;
		return $this;
	}
	
	protected $limit = 100;


	protected function build() {

		if ($this->import) {
			$this->where[] =  " import_url_result.import_url_id = :import_url_id ";
			$this->params['import_url_id'] = $this->import->getId();
		}
		
	}
	

	protected function buildStat() {

		
		
		$sql = "SELECT import_url_result.* FROM import_url_result ".
				" WHERE ".implode(" AND ", $this->where).
				" ORDER BY import_url_result.created_at DESC ".( $this->limit > 0 ? " LIMIT ". $this->limit : "");
	
		$this->stat = $this->app['db']->prepare($sql);
		$this->stat->execute($this->params);
	}
	
	

	public function fetchAll() {
		
		$this->buildStart();
		$this->build();
		$this->buildStat();
		

		
		$results = array();
		while($data = $this->stat->fetch()) {
			$importURLResult = new ImportResultModel();
			$importURLResult->setFromDataBaseRow($data);
			$results[] = $importURLResult;
		}
		return $results;
		
	}

}

