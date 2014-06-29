<?php

namespace repositories;

use models\ImportURLModel;
use models\ImportURLHistoryModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ImportURLHistoryRepository {

	
	public function loadByEventAndtimeStamp(ImportURLModel $importurl, $timestamp) {
		global $DB;
		$stat = $DB->prepare("SELECT import_url_history.* FROM import_url_history ".
				"WHERE import_url_history.import_url_id =:id AND import_url_history.created_at =:cat");
		$stat->execute(array( 'id'=>$importurl->getId(), 'cat'=>date("Y-m-d H:i:s",$timestamp) ));
		if ($stat->rowCount() > 0) {
			$importurl = new ImportURLHistoryModel();
			$importurl->setFromDataBaseRow($stat->fetch());
			return $importurl;
		}
	}
	
	public function ensureChangedFlagsAreSet(ImportURLHistoryModel $importurlhistory) {
		global $DB;
		
		// do we already have them?
		if (!$importurlhistory->isAnyChangeFlagsUnknown()) return;
		
		// load last.
		$stat = $DB->prepare("SELECT * FROM import_url_history WHERE import_url_id = :id AND created_at < :at ".
				"ORDER BY created_at DESC LIMIT 1");
		$stat->execute(array('id'=>$importurlhistory->getId(),'at'=>$importurlhistory->getCreatedAt()->format("Y-m-d H:i:s")));
		
		
		if ($stat->rowCount() == 0) {
			$importurlhistory->setChangedFlagsFromNothing();
		} else {
			$lastHistory = new ImportURLHistoryModel();
			$lastHistory->setFromDataBaseRow($stat->fetch());
			$importurlhistory->setChangedFlagsFromLast($lastHistory);
		}
		
		$statUpdate = $DB->prepare("UPDATE import_url_history SET ".
				" is_new = :is_new, ".
				" title_changed = :title_changed   , ".
				" is_enabled_changed = :is_enabled_changed   , ".
				" expired_at_changed = :expired_at_changed   , ".
				" area_id_changed = :area_id_changed   , ".
				" country_id_changed = :country_id_changed    ".
				"WHERE import_url_id = :id AND created_at = :created_at");
		$statUpdate->execute(array(
				'id'=>$importurlhistory->getId(),
				'created_at'=>$importurlhistory->getCreatedAt()->format("Y-m-d H:i:s"),
				'is_new'=>$importurlhistory->getIsNew()?1:0,
				'title_changed'=> $importurlhistory->getTitleChanged() ? 1 : -1,
				'is_enabled_changed'=> $importurlhistory->getIsEnabledChanged() ? 1 : -1,
				'expired_at_changed'=> $importurlhistory->getExpiredAtChanged() ? 1 : -1,
				'country_id_changed'=> $importurlhistory->getCountryIdChanged() ? 1 : -1,
				'area_id_changed'=> $importurlhistory->getAreaIdChanged() ? 1 : -1,
			));
	}
	
	
	
	
	public function loadByEventAndlastEditByUser(ImportURLModel $importurl, UserAccountModel $user) {
		global $DB;
		$stat = $DB->prepare("SELECT import_url_history.* FROM import_url_history ".
				" WHERE import_url_history.import_url_id = :id AND import_url_history.user_account_id = :user ".
				" ORDER BY import_url_history.created_at DESc");
		$stat->execute(array( 
				'id'=>$importurl->getId(), 
				'user'=>$user->getId() 
			));
		if ($stat->rowCount() > 0) {
			$importurl = new ImportURLHistoryModel();
			$importurl->setFromDataBaseRow($stat->fetch());
			return $importurl;
		}
	}
	
	
}


