<?php

namespace repositories;

use models\UserAccountModel;
use models\UserAccountGeneralSecurityKeyModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class UserAccountGeneralSecurityKeyRepository {
	
	/**
	 * This will always return something. If one doesn't exist, one will be created.
	 * @return UserAccountPrivateFeedKeyModel
	 */
	public function getForUser(UserAccountModel $user) {
		global $DB;
		
		$stat = $DB->prepare("SELECT * FROM user_account_general_security_key WHERE user_account_id=:uid");
		$stat->execute(array('uid'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			$uagskm = new UserAccountGeneralSecurityKeyModel();
			$uagskm->setFromDataBaseRow($stat->fetch());
			return $uagskm;
		}
		
		$uagskm = new UserAccountGeneralSecurityKeyModel();
		$uagskm->setUserAccountId($user->getId());
		$uagskm->setAccessKey(createKey(2,150));
		
		// TODO check not already used
		
		$stat = $DB->prepare("INSERT INTO user_account_general_security_key (user_account_id, access_key, created_at) ".
				"VALUES (:user_account_id, :access_key, :created_at)");
		$stat->execute(array(
				'user_account_id'=>$uagskm->getUserAccountId(),
				'access_key'=>$uagskm->getAccessKey(),
				'created_at'=>\TimeSource::getFormattedForDataBase()
			));
		
		return $uagskm;
		
	}
	
	/** @return UserAccountPrivateFeedKeyModel **/
	public function loadByUserAccountIDAndAccessKey($id, $access) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_general_security_key.* FROM user_account_general_security_key WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uagskm = new UserAccountGeneralSecurityKeyModel();
			$uagskm->setFromDataBaseRow($stat->fetch());
			return $uagskm;
		}
	}
	
}


