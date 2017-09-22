<?php


namespace repositories;

use models\UserAccountEditMetaDataModel;
use models\UserAccountModel;
use models\UserAccountResetModel;
use models\SiteModel;
use org\openacalendar\curatedlists\models\CuratedListModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountRepository {


    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function create(UserAccountModel $user, UserAccountEditMetaDataModel $userAccountEditMetaDataModel = null) {

		
		// TODO should check email and username not already exist and nice error

        if (!$user->getUsername()) {
            $user->setUsername(createKey($this->app['config']->createUserNameMinimumLength,$this->app['config']->createUserNameMaximumLength));
        }
		
		$stat = $this->app['db']->prepare("INSERT INTO user_account_information (username, username_canonical, email, email_canonical, displayname, password_hash, created_at, is_editor, created_from_ip) ".
				"VALUES (:username, :username_canonical, :email, :email_canonical, :displayname, :password_hash, :created_at, :is_editor, :created_from_ip) RETURNING id");
		$stat->execute(array(
				'username'=>substr($user->getUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
				'username_canonical'=> substr(UserAccountModel::makeCanonicalUserName($user->getUsername()),0,VARCHAR_COLUMN_LENGTH_USED), 
				'email'=>substr($user->getEmail(),0,VARCHAR_COLUMN_LENGTH_USED),
				'email_canonical'=>substr(UserAccountModel::makeCanonicalEmail($user->getEmail()),0,VARCHAR_COLUMN_LENGTH_USED),
				'displayname' => $user->getDisplayname(),
				'password_hash'=>$user->getPasswordHash(),
				'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
				'is_editor'=> $this->app['config']->newUsersAreEditors?1:0,
				'created_from_ip' => ($userAccountEditMetaDataModel ? $userAccountEditMetaDataModel->getIp() : null),
			));
		$data = $stat->fetch();
		$user->setId($data['id']);


        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));

		$this->app['extensionhookrunner']->afterUserAccountCreate($user);
	}
	
	
	
	public function loadByUserName(string $userName) {

		$stat = $this->app['db']->prepare("SELECT user_account_information.* FROM user_account_information WHERE username_canonical =:detail");
		$stat->execute(array( 'detail'=>UserAccountModel::makeCanonicalUserName($userName) ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}
	
	
	public function loadByEmail(string $email) {

		$stat = $this->app['db']->prepare("SELECT user_account_information.* FROM user_account_information WHERE email_canonical =:detail");
		$stat->execute(array( 'detail'=>UserAccountModel::makeCanonicalEmail($email) ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}
	
	
	public function loadByUserNameOrEmail(string $userNameOrEmail) {
		if (strpos($userNameOrEmail, "@") > 0) {
			return $this->loadByEmail($userNameOrEmail);
		} else {
			return $this->loadByUserName($userNameOrEmail);
		}
	}
	
	public function loadByID(int $userID) {

		$stat = $this->app['db']->prepare("SELECT user_account_information.* FROM user_account_information WHERE id =:id");
		$stat->execute(array( 'id'=>$userID ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}

	public function verifyEmail(UserAccountModel $user) {

		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  is_email_verified='t' WHERE id =:id");
		$stat->execute(array( 'id'=>$user->getId() ));
		$user->setIsEmailVerified(true);

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
	}
	
	public function resetAccount(UserAccountModel $user, UserAccountResetModel $reset ) {

		try {
			$this->app['db']->beginTransaction();
	
			$stat = $this->app['db']->prepare("UPDATE user_account_information SET  password_hash=:password_hash WHERE id =:id");
			$stat->execute(array( 
					'id'=>$user->getId() ,
					'password_hash'=>$user->getPasswordHash(),
				));

			
			$stat = $this->app['db']->prepare("UPDATE user_account_reset SET  reset_at=:reset_at WHERE user_account_id =:user_account_id AND access_key=:access_key");
			$stat->execute(array( 
					'user_account_id'=>$user->getId() ,
					'access_key'=>$reset->getAccessKey(),
					'reset_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			
			$this->app['db']->commit();

            $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));

		} catch (Exception $e) {
			$this->app['db']->rollBack();

		}
	}
	
	public function editPassword(UserAccountModel $user) {

	
		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  password_hash=:password_hash WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'password_hash'=>$user->getPasswordHash(),
			));


        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
			
	}

	/**
	 * This does not provide any audit logging and is for use by sys admins only.
	 * @param UserAccountModel $user
	 */
	public function editEmail(UserAccountModel $user) {


		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  email=:email, email_canonical=:email_canonical  WHERE id =:id");
		$stat->execute(array(
				'id'=>$user->getId() ,
				'email'=>substr($user->getEmail(),0,VARCHAR_COLUMN_LENGTH_USED),
				'email_canonical'=>substr(UserAccountModel::makeCanonicalEmail($user->getEmail()),0,VARCHAR_COLUMN_LENGTH_USED),
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));

	}
	
	
	
	public function edit(UserAccountModel $user) {

	
		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  is_editor=:is_editor, is_system_admin=:is_system_admin WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'is_editor'=>$user->getIsEditor()?1:0,
				'is_system_admin'=>$user->getIsSystemAdmin()?1:0,
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
			
	}
	
	public function editEmailsOptions(UserAccountModel $user) {

	
		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  email_upcoming_events=:email_upcoming_events, ".
				"email_upcoming_events_days_notice=:email_upcoming_events_days_notice ".
				"WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'email_upcoming_events'=>$user->getEmailUpcomingEvents(),
				'email_upcoming_events_days_notice'=>$user->getEmailUpcomingEventsDaysNotice(),
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
			
	}
	
	public function editPreferences(UserAccountModel $user) {

	
		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  is_clock_12hour=:is_clock_12hour ".
				"WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'is_clock_12hour'=>$user->getIsClock12Hour()?1:0,
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
	}
	
	public function systemAdminShuts(UserAccountModel $user, UserAccountModel $shutBy) {

		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  is_closed_by_sys_admin='1' WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
	}
	
	public function systemAdminOpens(UserAccountModel $user, UserAccountModel $shutBy) {

		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  is_closed_by_sys_admin='0' WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
	}
	
	public function hasMadeAnyEdits(UserAccountModel $user) {


		// For performance reasons, may as well put most likely hit first
		
		// Events
		$stat = $this->app['db']->prepare("SELECT event_id FROM event_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}
		
		// Groups
		$stat = $this->app['db']->prepare("SELECT group_id FROM group_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}
		
		// Venues
		$stat = $this->app['db']->prepare("SELECT venue_id FROM venue_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}

		// Site
		$stat = $this->app['db']->prepare("SELECT site_id FROM site_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}
				
		// No :-(
		return false;
	}
	
	
	public function makeSysAdmin(UserAccountModel $user, UserAccountModel $madeBy=null) {

		$stat = $this->app['db']->prepare("UPDATE user_account_information SET  is_system_admin='1', ".
			"is_editor='1', is_closed_by_sys_admin='0' WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
			));

        $this->app['messagequeproducerhelper']->send('org.openacalendar', 'UserSaved', array('user_account_id'=>$user->getId()));
	}
	
}

