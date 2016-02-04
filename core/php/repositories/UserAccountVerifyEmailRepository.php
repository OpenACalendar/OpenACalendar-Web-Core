<?php

namespace repositories;

use models\UserAccountModel;
use models\UserAccountVerifyEmailModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class UserAccountVerifyEmailRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

	public function create(UserAccountModel $user) {

		
		$uavem = new UserAccountVerifyEmailModel();
		$uavem->setEmail($user->getEmail());
		$uavem->setUserAccountId($user->getId());
		$uavem->setAccessKey(createKey(2,250));
		
		// TODO check not already used
		
		$stat = $this->app['db']->prepare("INSERT INTO user_account_verify_email (user_account_id, email, access_key, created_at) ".
				"VALUES (:user_account_id, :email, :access_key, :created_at)");
		$stat->execute(array(
				'user_account_id'=>$uavem->getUserAccountId(),
				'access_key'=>$uavem->getAccessKey(),
				'email'=>substr($uavem->getEmail(),0,VARCHAR_COLUMN_LENGTH_USED),
				'created_at'=>$this->app['timesource']->getFormattedForDataBase()
			));
		
		return $uavem;
		
	}
	
	/** @return UserAccountRememberMeModel **/
	public function loadByUserAccountIDAndAccessKey($id, $access) {

		$stat = $this->app['db']->prepare("SELECT user_account_verify_email.* FROM user_account_verify_email WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uavem = new UserAccountVerifyEmailModel();
			$uavem->setFromDataBaseRow($stat->fetch());
			return $uavem;
		}
	}
	
	public function markVerifiedByUserAccountIDAndAccessKey($id, $access, $fromIP = null) {

		
		try {
			$this->app['db']->beginTransaction();
			
			$stat = $this->app['db']->prepare("UPDATE user_account_verify_email SET verified_at=:verified_at, verified_from_ip=:verified_from_ip WHERE user_account_id =:user_account_id AND access_key=:access_key");
			$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access, 'verified_at'=> $this->app['timesource']->getFormattedForDataBase(), 'verified_from_ip'=>$fromIP));
			
			$stat = $this->app['db']->prepare("UPDATE user_account_information SET  is_email_verified='1' WHERE id =:id");
			$stat->execute(array( 'id'=>$id ));
		
			$this->app['db']->commit();
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}	
	}
	
	
	public function getLastSentForUserAccount(UserAccountModel $user) {

		$stat = $this->app['db']->prepare("SELECT MAX(created_at) AS c FROM user_account_verify_email WHERE user_account_id=:user_account_id");
		$stat->execute(array('user_account_id'=>$user->getId()));
		$data = $stat->fetch();
		return $data['c'] ? new \DateTime($data['c'], new \DateTimeZone('UTC')) : null;
	}
	
}