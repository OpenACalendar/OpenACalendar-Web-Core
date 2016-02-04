<?php

namespace repositories;

use models\UserAccountModel;
use models\UserAccountResetModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountResetRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function create(UserAccountModel $user) {

		
		$uar = new UserAccountResetModel();
		$uar->setUserAccountId($user->getId());
		$uar->setAccessKey(createKey(2,250));
		
		// TODO check not already used
		
		$stat = $this->app['db']->prepare("INSERT INTO user_account_reset (user_account_id, access_key, created_at) ".
				"VALUES (:user_account_id, :access_key, :created_at)");
		$stat->execute(array(
				'user_account_id'=>$uar->getUserAccountId(),
				'access_key'=>$uar->getAccessKey(),
				'created_at'=>$this->app['timesource']->getFormattedForDataBase()
			));
		$data = $stat->fetch();
		
		return $uar;
		
	}
	
	/** @return UserAccountResetModel **/
	public function loadByUserAccountIDAndAccessKey($id, $access) {

		$stat = $this->app['db']->prepare("SELECT user_account_reset.* FROM user_account_reset WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uar = new UserAccountResetModel();
			$uar->setFromDataBaseRow($stat->fetch());
			return $uar;
		}
	}
	
	/**
	 * 
	 * @return \models\UserAccountResetModel A single one or NULL. Technically it may load multiple ones, but we only return one.
	 */
	public function loadRecentlyUnusedSentForUserAccountId($id, $seconds= 60) {

		$stat = $this->app['db']->prepare("SELECT user_account_reset.* FROM user_account_reset WHERE reset_at IS NULL AND user_account_id =:user_account_id AND created_at > :since");
		$time = $this->app['timesource']->getDateTime();
		$time->setTimestamp($time->getTimestamp() - $seconds);
		$stat->execute(array( 'user_account_id'=>$id,'since'=>$time->format('Y-m-d H:i:s') ));
		if ($stat->rowCount() > 0) {
			$uar = new UserAccountResetModel();
			$uar->setFromDataBaseRow($stat->fetch());
			return $uar;
		}		
	}
	
}

