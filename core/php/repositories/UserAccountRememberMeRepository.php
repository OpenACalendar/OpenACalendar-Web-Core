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
	public function loadByUserAccountIDAndAccessKey(int $id, string $access) {

		$stat = $this->app['db']->prepare("SELECT user_account_remember_me.* FROM user_account_remember_me WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uarm = new UserAccountRememberMeModel();
			$uarm->setFromDataBaseRow($stat->fetch());
			return $uarm;
		}
	}
	
	public function deleteByUserAccountIDAndAccessKey(int $id, string $access) {

		$stat = $this->app['db']->prepare("DELETE FROM user_account_remember_me WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
	}

    public function editLastUsed(UserAccountRememberMeModel $userAccountRememberMeModel) {

        $stat = $this->app['db']->prepare("UPDATE user_account_remember_me SET  last_used_at=:last_used_at ".
            "WHERE user_account_id = :user_account_id AND access_key = :access_key");
        $stat->execute(array(
            'last_used_at'=>$this->app['timesource']->getFormattedForDataBase() ,
            'user_account_id'=>$userAccountRememberMeModel->getUserAccountId(),
            'access_key' => $userAccountRememberMeModel->getAccessKey(),
        ));

    }

    public function getLastUsedForUser(UserAccountModel $userAccountModel) {

        $stat = $this->app['db']->prepare("SELECT user_account_remember_me.last_used_at FROM user_account_remember_me WHERE ".
            "user_account_id =:user_account_id AND last_used_at IS NOT NULL ORDER BY last_used_at DESC");
        $stat->execute(array( 'user_account_id'=>$userAccountModel->getId() ));
        if ($stat->rowCount() > 0) {
            $data = $stat->fetch();
            return $data['last_used_at'];
        }

    }

}