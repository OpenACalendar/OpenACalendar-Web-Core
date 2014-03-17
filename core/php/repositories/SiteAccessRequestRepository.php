<?php


namespace repositories;

use models\SiteModel;
use models\UserAccountModel;
use repositories\UserInSiteRepository;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteAccessRequestRepository {

	function create(SiteModel $site, UserAccountModel $user, $answer) {
		global $DB;
		$stat = $DB->prepare("INSERT INTO site_access_request (site_id, user_account_id, answer,created_at,created_by) ".
				"VALUES (:site_id, :user_account_id, :answer,:created_at,:created_by)");  //  RETURNING id
		$stat->execute(array(
				'site_id'=>$site->getId(), 
				'user_account_id'=>$user->getId(),
				'answer'=>$answer,
				'created_by'=>$user->getId(),
				'created_at'=>\TimeSource::getFormattedForDataBase()
			));
		//$data = $stat->fetch();
		//$group->setId($data['id']);		
	}
	
	function grantForSiteAndUser(SiteModel $site, UserAccountModel $user, UserAccountModel $grantedBy) {
		global $DB;
		
		$repo = new UserInSiteRepository();
		$repo->markUserEditsSite($user, $site);
		
		$stat = $DB->prepare("UPDATE site_access_request SET granted_at=:granted_at, granted_by=:granted_by WHERE ".
				" site_id=:site_id AND  user_account_id=:user_account_id AND granted_by IS NULL AND rejected_by IS NULL");
		$stat->execute(array(
				'site_id'=>$site->getId(), 
				'user_account_id'=>$user->getId(),
				'granted_by'=>$grantedBy->getId(),
				'granted_at'=>\TimeSource::getFormattedForDataBase()
			));
	}
	
	function rejectForSiteAndUser(SiteModel $site, UserAccountModel $user, UserAccountModel $rejectedBy) {
		global $DB;
		$stat = $DB->prepare("UPDATE site_access_request SET rejected_at=:rejected_at, rejected_by=:rejected_by WHERE ".
				" site_id=:site_id AND  user_account_id=:user_account_id AND granted_by IS NULL AND rejected_by IS NULL");
		$stat->execute(array(
				'site_id'=>$site->getId(), 
				'user_account_id'=>$user->getId(),
				'rejected_by'=>$rejectedBy->getId(),
				'rejected_at'=>\TimeSource::getFormattedForDataBase()
			));
	}
	
	function isCurrentRequestExistsForSiteAndUser(SiteModel $site, UserAccountModel $user) {
		global $DB;
		
		$stat = $DB->prepare("SELECT * FROM site_access_request WHERE ".
				" site_id=:site_id AND  user_account_id=:user_account_id AND granted_by IS NULL AND rejected_by IS NULL");
		$stat->execute(array(
				'site_id'=>$site->getId(), 
				'user_account_id'=>$user->getId(),
			));
		return $stat->rowCount() > 0;
	}
	
}

