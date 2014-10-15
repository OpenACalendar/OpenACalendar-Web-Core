<?php

namespace repositories;

use models\API2ApplicationModel;
use models\API2ApplicationRequestTokenModel;
use models\UserAccountModel;
use models\API2ApplicationUserAuthorisationTokenModel;
use models\API2ApplicationUserPermissionsModel;
use models\UserInAPI2ApplicationModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserInAPI2ApplicationRepository {
	
	
	public function setPermissionsForUserInApp(API2ApplicationUserPermissionsModel $permissions, UserAccountModel $user, API2ApplicationModel $app) {
		global $DB;
		
		$stat = $DB->prepare("SELECT user_in_api2_application_information.* FROM user_in_api2_application_information WHERE ".
				"api2_application_id =:api2_application_id AND user_id =:user_id");
		$stat->execute(array( 'api2_application_id'=>$app->getId(), 'user_id'=>$user->getId() ));
		
		################## If not there, just add
		if ($stat->rowCount() == 0) {
			$stat = $DB->prepare("INSERT INTO user_in_api2_application_information ".
					"(api2_application_id, user_id, is_editor, created_at) ".
					"VALUES (:api2_application_id, :user_id, :is_editor, :created_at)");
			
			$stat->execute(array(
				'api2_application_id'=>$app->getId(),
				'user_id'=> $user->getId() ,
				'is_editor'=>  $permissions->getIsEditorGranted() ? 1 : 0,
				'created_at'=>  \TimeSource::getFormattedForDataBase(),
			));
			
			return;
		}	
			
		################## get data, check if we need to escalate or remove permissions
		$userInAppData = $stat->fetch();

		if (($permissions->getIsEditorGranted() && $userInAppData['is_editor'] == 0)) {
			
			$stat = $DB->prepare("UPDATE user_in_api2_application_information ".
					" SET is_editor='1' ".
					" WHERE api2_application_id =:api2_application_id AND user_id =:user_id ");
			$stat->execute(array( 
					'api2_application_id'=>$app->getId(), 
					'user_id'=>$user->getId() ,
				));
			
		}

		if (($permissions->getIsEditorRefused() && $userInAppData['is_editor'] == 1)) {

			$stat = $DB->prepare("UPDATE user_in_api2_application_information ".
					" SET is_editor='0' ".
					" WHERE api2_application_id =:api2_application_id AND user_id =:user_id ");
			$stat->execute(array(
					'api2_application_id'=>$app->getId(),
					'user_id'=>$user->getId() ,
				));

		}

	}
	
	
	/** 
	 * 
	 * @return \models\UserInAPI2ApplicationModel
	 */
	public function loadByUserAndApplication(UserAccountModel $user, API2ApplicationModel $app) {
		global $DB;
		$stat = $DB->prepare("SELECT user_in_api2_application_information.* FROM user_in_api2_application_information ".
				"WHERE api2_application_id =:api2_application_id AND user_id =:user_id");
		$stat->execute(array( 
				'api2_application_id'=>$app->getId(), 
				'user_id'=>$user->getId()
			));
		if ($stat->rowCount() > 0) {
			$app = new UserInAPI2ApplicationModel();
			$app->setFromDataBaseRow($stat->fetch());
			return $app;
		}
	}
	
	
}

