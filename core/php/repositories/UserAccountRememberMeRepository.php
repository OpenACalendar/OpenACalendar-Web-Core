<?php

namespace repositories;

use models\UserAccountModel;
use models\UserAccountRememberMeModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountRememberMeRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function create(UserAccountModel $user) {

		
		$uarm = new UserAccountRememberMeModel();
		$uarm->setUserAccountId($user->getId());
		$uarm->setAccessKey(createKey(2,250));
		
		// TODO check not already used
		
		$stat = $this->app['db']->prepare("INSERT INTO user_account_remember_me (user_account_id, access_key, created_at) ".
				"VALUES (:user_account_id, :access_key, :created_at)");
		$stat->execute(array(
				'user_account_id'=>$uarm->getUserAccountId(),
				'access_key'=>$uarm->getAccessKey(),
				'created_at'=>$this->app['timesource']->getFormattedForDataBase()
			));
		$data = $stat->fetch();
		
		return $uarm;
		
	}
	
	/** @return UserAccountRememberMeModel **/
	public function loadByUserAccountIDAndAccessKey($id, $access) {

		$stat = $this->app['db']->prepare("SELECT user_account_remember_me.* FROM user_account_remember_me WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uarm = new UserAccountRememberMeModel();
			$uarm->setFromDataBaseRow($stat->fetch());
			return $uarm;
		}
	}
	
	public function deleteByUserAccountIDAndAccessKey($id, $access) {

		$stat = $this->app['db']->prepare("DELETE FROM user_account_remember_me WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
	}
}