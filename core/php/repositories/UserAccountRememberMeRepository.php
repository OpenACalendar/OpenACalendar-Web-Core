<?php

namespace repositories;

use models\UserAccountModel;
use models\UserAccountRememberMeModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountRememberMeRepository {
	
	public function create(UserAccountModel $user) {
		global $DB;
		
		$uarm = new UserAccountRememberMeModel();
		$uarm->setUserAccountId($user->getId());
		$uarm->setAccessKey(createKey(2,250));
		
		// TODO check not already used
		
		$stat = $DB->prepare("INSERT INTO user_account_remember_me (user_account_id, access_key, created_at) ".
				"VALUES (:user_account_id, :access_key, :created_at)");
		$stat->execute(array(
				'user_account_id'=>$uarm->getUserAccountId(),
				'access_key'=>$uarm->getAccessKey(),
				'created_at'=>\TimeSource::getFormattedForDataBase()
			));
		$data = $stat->fetch();
		
		return $uarm;
		
	}
	
	/** @return UserAccountRememberMeModel **/
	public function loadByUserAccountIDAndAccessKey($id, $access) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_remember_me.* FROM user_account_remember_me WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uarm = new UserAccountRememberMeModel();
			$uarm->setFromDataBaseRow($stat->fetch());
			return $uarm;
		}
	}
	
	public function deleteByUserAccountIDAndAccessKey($id, $access) {
		global $DB;
		$stat = $DB->prepare("DELETE FROM user_account_remember_me WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
	}
}