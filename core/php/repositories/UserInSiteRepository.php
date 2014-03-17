<?php

namespace repositories;

use models\UserInSiteModel;
use models\UserAccountModel;
use models\SiteModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserInSiteRepository {
	
	/** @return UserInSiteModel **/
	public function loadBySiteAndUserAccount(SiteModel $site, UserAccountModel $userAccount) {
		global $DB;
		$stat = $DB->prepare("SELECT user_in_site_information.* FROM user_in_site_information WHERE user_account_id =:user_account_id AND site_id=:site_id");
		$stat->execute(array( 'user_account_id'=>$userAccount->getId(), 'site_id'=>$site->getId() ));
		if ($stat->rowCount() > 0) {
			$uar = new UserInSiteModel();
			$uar->setFromDataBaseRow($stat->fetch());
			return $uar;
		}
	}
	
	
	
	public function markUserAdministratesSite(UserAccountModel $userAccount, SiteModel $site) {
			$this->markUserPermissionInSite($userAccount, $site, "is_administrator");
	}

	public function markUserEditsSite(UserAccountModel $userAccount, SiteModel $site) {
			$this->markUserPermissionInSite($userAccount, $site, "is_editor");
	}
	
	
	
	private  function markUserPermissionInSite(UserAccountModel $userAccount, SiteModel $site, $permission) {
		global $DB;
		$useTransaction = !$DB->inTransaction();
		try {
			if ($useTransaction) $DB->beginTransaction();
			
			$stat = $DB->prepare("SELECT ".$permission." FROM user_in_site_information WHERE user_account_id =:user_account_id AND site_id=:site_id ");
			$stat->execute(array( 'user_account_id'=>$userAccount->getId(), 'site_id'=>$site->getId() ));
			
			if ($stat->rowCount() == 0) {
				
				$stat = $DB->prepare("INSERT INTO user_in_site_information (user_account_id, site_id, ".$permission.", created_at) ".
						"VALUES (:user_account_id, :site_id, :per, :created_at)");
				$stat->execute(array(
						'user_account_id'=>$userAccount->getId(), 
						'site_id'=>$site->getId(), 
						'per'=>1, 
						'created_at'=>  \TimeSource::getFormattedForDataBase()
					));
				
			} else {
				
				
				$data = $stat->fetch();
				if (!$data[$permission]) {
						$stat = $DB->prepare("UPDATE user_in_site_information  SET ".$permission." = '1' WHERE  user_account_id =:user_account_id AND site_id=:site_id");
						$stat->execute(array(
								'user_account_id'=>$userAccount->getId(), 
								'site_id'=>$site->getId(), 
							));
				
				}
				
			}
			
			if ($useTransaction) $DB->commit();
		} catch (Exception $e) {
			if ($useTransaction) $DB->rollback();
		}
	}

	
	
	public function removeUserEditsSite(UserAccountModel $userAccount, SiteModel $site) {
		$this->removeUserPermissionSite($userAccount, $site, "is_editor");
	}

	public function removeUserAdministratesSite(UserAccountModel $userAccount, SiteModel $site) {
		$this->removeUserPermissionSite($userAccount, $site, "is_administrator");
	}	
	
	protected function removeUserPermissionSite(UserAccountModel $userAccount, SiteModel $site, $permission) {
		global $DB;
		$useTransaction = !$DB->inTransaction();
		try {
			if ($useTransaction) $DB->beginTransaction();
			
			$stat = $DB->prepare("SELECT ".$permission." FROM user_in_site_information WHERE user_account_id =:user_account_id AND site_id=:site_id ");
			$stat->execute(array( 'user_account_id'=>$userAccount->getId(), 'site_id'=>$site->getId() ));
			
			if ($stat->rowCount() > 0) {
				$data = $stat->fetch();
				if ($data[$permission]) {
					$stat = $DB->prepare("UPDATE user_in_site_information  SET ".$permission." = '0' WHERE  user_account_id =:user_account_id AND site_id=:site_id");
					$stat->execute(array(
							'user_account_id'=>$userAccount->getId(), 
							'site_id'=>$site->getId(), 
						));
				}
			}
			
			if ($useTransaction) $DB->commit();
		} catch (Exception $e) {
			if ($useTransaction) $DB->rollback();
		}
	}	

	public  function setUserOwnsSite(UserAccountModel $userAccount, SiteModel $site) {
		global $DB;
		$useTransaction = !$DB->inTransaction();
		try {
			if ($useTransaction) $DB->beginTransaction();
			
			// Make this user owner
			$stat = $DB->prepare("SELECT is_owner FROM user_in_site_information WHERE user_account_id =:user_account_id AND site_id=:site_id ");
			$stat->execute(array( 'user_account_id'=>$userAccount->getId(), 'site_id'=>$site->getId() ));
			
			if ($stat->rowCount() == 0) {
				
				$stat = $DB->prepare("INSERT INTO user_in_site_information (user_account_id, site_id, is_owner, created_at) ".
						"VALUES (:user_account_id, :site_id, :per, :created_at)");
				$stat->execute(array(
						'user_account_id'=>$userAccount->getId(), 
						'site_id'=>$site->getId(), 
						'per'=>1, 
						'created_at'=>  \TimeSource::getFormattedForDataBase()
					));
				
			} else {
				
				
				$data = $stat->fetch();
				if (!$data['is_owner']) {
						$stat = $DB->prepare("UPDATE user_in_site_information  SET is_owner = '1' WHERE  user_account_id =:user_account_id AND site_id=:site_id");
						$stat->execute(array(
								'user_account_id'=>$userAccount->getId(), 
								'site_id'=>$site->getId(), 
							));
				
				}
				
			}
			
			// Make old owners not owners. Make them admins, don't just let them vanish.
			$stat = $DB->prepare("UPDATE user_in_site_information  SET is_owner = '0', is_administrator='1' ".
					"WHERE  user_account_id !=:user_account_id AND site_id=:site_id AND is_owner='1'");
			$stat->execute(array(
					'user_account_id'=>$userAccount->getId(), 
					'site_id'=>$site->getId(), 
				));
			
			if ($useTransaction) $DB->commit();
		} catch (Exception $e) {
			if ($useTransaction) $DB->rollback();
		}
	}

	
}
