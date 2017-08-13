<?php

namespace repositories;

use models\UserAccountModel;
use models\UserAccountPrivateFeedKeyModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountPrivateFeedKeyRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }


    /**
	 * This will always return something. If one doesn't exist, one will be created.
	 * @return UserAccountPrivateFeedKeyModel
	 */
	public function getForUser(UserAccountModel $user) {

		
		$stat = $this->app['db']->prepare("SELECT * FROM user_account_private_feed_key WHERE user_account_id=:uid");
		$stat->execute(array('uid'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			$uapfkm = new UserAccountPrivateFeedKeyModel();
			$uapfkm->setFromDataBaseRow($stat->fetch());
			return $uapfkm;
		}
		
		$uapfkm = new UserAccountPrivateFeedKeyModel();
		$uapfkm->setUserAccountId($user->getId());
		$uapfkm->setAccessKey(createKey(2,150));
		
		// TODO check not already used
		
		$stat = $this->app['db']->prepare("INSERT INTO user_account_private_feed_key (user_account_id, access_key, created_at) ".
				"VALUES (:user_account_id, :access_key, :created_at)");
		$stat->execute(array(
				'user_account_id'=>$uapfkm->getUserAccountId(),
				'access_key'=>$uapfkm->getAccessKey(),
				'created_at'=>$this->app['timesource']->getFormattedForDataBase()
			));
		
		return $uapfkm;
		
	}
	
	/** @return UserAccountPrivateFeedKeyModel **/
	public function loadByUserAccountIDAndAccessKey(int $id, string $access) {

		$stat = $this->app['db']->prepare("SELECT user_account_private_feed_key.* FROM user_account_private_feed_key WHERE user_account_id =:user_account_id AND access_key=:access_key");
		$stat->execute(array( 'user_account_id'=>$id, 'access_key'=>$access ));
		if ($stat->rowCount() > 0) {
			$uapfkm = new UserAccountPrivateFeedKeyModel();
			$uapfkm->setFromDataBaseRow($stat->fetch());
			return $uapfkm;
		}
	}
	
}


