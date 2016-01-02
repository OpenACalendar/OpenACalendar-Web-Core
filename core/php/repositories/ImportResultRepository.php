<?php


namespace repositories;

use models\ImportResultModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportResultRepository {
	
	
	public function create(ImportResultModel $importURLResult) {
		global $DB;

		$stat = $DB->prepare("INSERT INTO import_url_result (import_url_id,new_count,existing_count,saved_count,in_past_count,to_far_in_future_count,not_valid_count,created_at,is_success,message) ".
				"VALUES (:import_url_id,:new_count,:existing_count,:saved_count,:in_past_count,:to_far_in_future_count,:not_valid_count,:created_at,:is_success,:message)");
		$stat->execute(array(
				'import_url_id'=>$importURLResult->getImportId(),
				'new_count'=>$importURLResult->getNewCount(), 
				'existing_count'=>$importURLResult->getExistingCount(), 
				'saved_count'=>$importURLResult->getSavedCount(), 
				'in_past_count'=>$importURLResult->getInPastCount(), 
				'to_far_in_future_count'=>$importURLResult->getToFarInFutureCount(), 
				'not_valid_count'=>$importURLResult->getNotValidCount(), 
				'created_at'=>\TimeSource::getFormattedForDataBase(), 
				'is_success'=>$importURLResult->getIsSuccess()?1:0,
				'message'=>$importURLResult->getMessage(),
			));
	}

}


