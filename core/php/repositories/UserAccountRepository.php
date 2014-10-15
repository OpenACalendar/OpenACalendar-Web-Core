<?php


namespace repositories;

use models\UserAccountModel;
use models\UserAccountResetModel;
use models\SiteModel;
use models\CuratedListModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountRepository {
	
	
	public function create(UserAccountModel $user) {
		global $DB, $CONFIG;
		
		
		// TODO should check email and username not already exist and nice error
			
		
		$stat = $DB->prepare("INSERT INTO user_account_information (username, username_canonical, email, email_canonical, password_hash, created_at, is_editor) ".
				"VALUES (:username, :username_canonical, :email, :email_canonical, :password_hash, :created_at, :is_editor) RETURNING id");
		$stat->execute(array(
				'username'=>substr($user->getUsername(),0,VARCHAR_COLUMN_LENGTH_USED),
				'username_canonical'=> substr(UserAccountModel::makeCanonicalUserName($user->getUsername()),0,VARCHAR_COLUMN_LENGTH_USED), 
				'email'=>substr($user->getEmail(),0,VARCHAR_COLUMN_LENGTH_USED),
				'email_canonical'=>substr(UserAccountModel::makeCanonicalEmail($user->getEmail()),0,VARCHAR_COLUMN_LENGTH_USED),
				'password_hash'=>$user->getPasswordHash(),
				'created_at'=>\TimeSource::getFormattedForDataBase(),
				'is_editor'=> $CONFIG->newUsersAreEditors?1:0,
			));
		$data = $stat->fetch();
		$user->setId($data['id']);
	}
	
	
	
	public function loadByUserName($userName) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_information.* FROM user_account_information WHERE username_canonical =:detail");
		$stat->execute(array( 'detail'=>UserAccountModel::makeCanonicalUserName($userName) ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}
	
	
	public function loadByEmail($email) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_information.* FROM user_account_information WHERE email_canonical =:detail");
		$stat->execute(array( 'detail'=>UserAccountModel::makeCanonicalEmail($email) ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}
	
	
	public function loadByUserNameOrEmail($userNameOrEmail) {
		if (strpos($userNameOrEmail, "@") > 0) {
			return $this->loadByEmail($userNameOrEmail);
		} else {
			return $this->loadByUserName($userNameOrEmail);
		}
	}
	
	public function loadByID($userID) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_information.* FROM user_account_information WHERE id =:id");
		$stat->execute(array( 'id'=>$userID ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}

	public function loadByOwnerOfCuratedList(CuratedListModel $curatedList) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_information.* FROM user_account_information ".
				" JOIN user_in_curated_list_information ON user_in_curated_list_information.user_account_id = user_account_information.id ".
				"WHERE user_in_curated_list_information.curated_list_id = :id AND user_in_curated_list_information.is_owner = 't'");
		$stat->execute(array( 'id'=>$curatedList->getId() ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}

	public function verifyEmail(UserAccountModel $user) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_account_information SET  is_email_verified='t' WHERE id =:id");
		$stat->execute(array( 'id'=>$user->getId() ));
		$user->setIsEmailVerified(true);
	}
	
	public function resetAccount(UserAccountModel $user, UserAccountResetModel $reset ) {
		global $DB;
		try {
			$DB->beginTransaction();
	
			$stat = $DB->prepare("UPDATE user_account_information SET  password_hash=:password_hash WHERE id =:id");
			$stat->execute(array( 
					'id'=>$user->getId() ,
					'password_hash'=>$user->getPasswordHash(),
				));

			
			$stat = $DB->prepare("UPDATE user_account_reset SET  reset_at=:reset_at WHERE user_account_id =:user_account_id AND access_key=:access_key");
			$stat->execute(array( 
					'user_account_id'=>$user->getId() ,
					'access_key'=>$reset->getAccessKey(),
					'reset_at'=>\TimeSource::getFormattedForDataBase(),
				));
			
			$DB->commit();

		} catch (Exception $e) {
			$DB->rollBack();

		}
	}
	
	public function editPassword(UserAccountModel $user) {
		global $DB;
	
		$stat = $DB->prepare("UPDATE user_account_information SET  password_hash=:password_hash WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'password_hash'=>$user->getPasswordHash(),
			));

			
	}
	
	
	
	public function edit(UserAccountModel $user) {
		global $DB;
	
		$stat = $DB->prepare("UPDATE user_account_information SET  is_editor=:is_editor, is_system_admin=:is_system_admin WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'is_editor'=>$user->getIsEditor()?1:0,
				'is_system_admin'=>$user->getIsSystemAdmin()?1:0,
			));

			
	}
	
	public function editEmailsOptions(UserAccountModel $user) {
		global $DB;
	
		$stat = $DB->prepare("UPDATE user_account_information SET  email_upcoming_events=:email_upcoming_events, ".
				"email_upcoming_events_days_notice=:email_upcoming_events_days_notice ".
				"WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'email_upcoming_events'=>$user->getEmailUpcomingEvents(),
				'email_upcoming_events_days_notice'=>$user->getEmailUpcomingEventsDaysNotice(),
			));

			
	}
	
	public function editPreferences(UserAccountModel $user) {
		global $DB;
	
		$stat = $DB->prepare("UPDATE user_account_information SET  is_clock_12hour=:is_clock_12hour ".
				"WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'is_clock_12hour'=>$user->getIsClock12Hour()?1:0,
			));

			
	}
	
	public function systemAdminShuts(UserAccountModel $user, UserAccountModel $shutBy, $reason) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_account_information SET  is_closed_by_sys_admin='1', closed_by_sys_admin_reason=:reason WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
				'reason'=>$reason,
			));
	}
	
	public function systemAdminOpens(UserAccountModel $user, UserAccountModel $shutBy) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_account_information SET  is_closed_by_sys_admin='0' WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
			));
	}
	
	public function hasMadeAnyEdits(UserAccountModel $user) {
		global $DB;

		// For performance reasons, may as well put most likely hit first
		
		// Events
		$stat = $DB->prepare("SELECT event_id FROM event_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}
		
		// Groups
		$stat = $DB->prepare("SELECT group_id FROM group_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}
		
		// Venues
		$stat = $DB->prepare("SELECT venue_id FROM venue_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}

		// Site
		$stat = $DB->prepare("SELECT site_id FROM site_history WHERE user_account_id=:id");
		$stat->execute(array('id'=>$user->getId()));
		if ($stat->rowCount() > 0) {
			return true;
		}
				
		// No :-(
		return false;
	}
	
	
	public function makeSysAdmin(UserAccountModel $user, UserAccountModel $madeBy=null) {
		global $DB;
		$stat = $DB->prepare("UPDATE user_account_information SET  is_system_admin='1', ".
			"is_editor='1', is_closed_by_sys_admin='0' WHERE id =:id");
		$stat->execute(array( 
				'id'=>$user->getId() ,
			));
	}
	
}

