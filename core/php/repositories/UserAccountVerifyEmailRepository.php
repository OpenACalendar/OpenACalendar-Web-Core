<?php

namespace repositories;

use models\UserAccountModel;
use models\UserAccountVerifyEmailModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class UserAccountVerifyEmailRepository {
	
	public function create(UserAccountModel $user) {
		global $DB;
		
		$uavem = new UserAccountVerifyEmailModel();
		$uavem->setEmail($user->getEmail());
		$uavem->setUserAccountId($user->getId());
		$uavem->setAccessKey(createKey(2,250));
		
		// TODO check not already used
		
		$stat = $DB->prepare("INSERT INTO user_account_verify_email (user_account_id, email, access_key, created_at) ".
				"VALUES (:user_account_id, :email, :access_key, :created_at)");
		$stat->execute(array(
				'user_account_id'=>$uavem->getUserAccountId(),
				'access_key'=>$uavem->getAccessKey(),
				'email'=>substr($uavem->getEmail(),0,VARCHAR_COLUMN_LENGTH_USED),
				'created_at'=>\TimeSource::getFormattedForDataBase()
			));
		
		return $uavem;
		
	}
	
	/** @return UserAccountRememberMeModel **/
	public function loadByUserAccountIDAndAccessKey($id, $access) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_verify_email.* FROM user_account_verify_email WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uavem = new UserAccountVerifyEmailModel();
			$uavem->setFromDataBaseRow($stat->fetch());
			return $uavem;
		}
	}
	
	public function markVerifiedByUserAccountIDAndAccessKey($id, $access, $fromIP = null) {
		global $DB;
		
		try {
			$DB->beginTransaction();	
			
			$stat = $DB->prepare("UPDATE user_account_verify_email SET verified_at=:verified_at, verified_from_ip=:verified_from_ip WHERE user_account_id =:user_account_id AND access_key=:access_key");
			$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access, 'verified_at'=> \TimeSource::getFormattedForDataBase(), 'verified_from_ip'=>$fromIP));
			
			$stat = $DB->prepare("UPDATE user_account_information SET  is_email_verified='1' WHERE id =:id");
			$stat->execute(array( 'id'=>$id ));
		
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}	
	}
	
	
	public function getLastSentForUserAccount(UserAccountModel $user) {
		global $DB;
		$stat = $DB->prepare("SELECT MAX(created_at) AS c FROM user_account_verify_email WHERE user_account_id=:user_account_id");
		$stat->execute(array('user_account_id'=>$user->getId()));
		$data = $stat->fetch();
		return $data['c'] ? new \DateTime($data['c'], new \DateTimeZone('UTC')) : null;
	}
	
}