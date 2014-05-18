<?php

namespace repositories;

use models\API2ApplicationModel;
use models\API2ApplicationUserTokenModel;
use models\UserAccountModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationUserTokenRepository {

	
	/** 
	 * 
	 * @return \models\API2ApplicationUserTokenModel
	 */
	public function loadByAppAndUserID(API2ApplicationModel $app, $userID) {
		global $DB;
		$stat = $DB->prepare("SELECT api2_application_user_token_information.*, user_in_api2_application_information.is_write_user_actions, ".
				" user_in_api2_application_information.is_write_user_profile, user_in_api2_application_information.is_write_calendar ".
				" FROM api2_application_user_token_information".
				" JOIN user_in_api2_application_information ON user_in_api2_application_information.is_in_app = '1' ".
				" AND user_in_api2_application_information.user_id = api2_application_user_token_information.user_id ".
				" AND user_in_api2_application_information.api2_application_id = api2_application_user_token_information.api2_application_id ".
				" WHERE api2_application_user_token_information.api2_application_id = :api2_application_id AND api2_application_user_token_information.user_id = :user_id");
		$stat->execute(array( 
				'api2_application_id'=>$app->getId(), 
				'user_id'=>$userID 
			));
		if ($stat->rowCount() > 0) {
			$token = new API2ApplicationUserTokenModel();
			$token->setFromDataBaseRow($stat->fetch());
			return $token;
		}
	}
	
	/** 
	 * 
	 * @return \models\API2ApplicationUserTokenModel
	 */
	public function loadByAppAndUserTokenAndUserSecret(API2ApplicationModel $app, $userToken, $userSecret) {
		global $DB;
		$stat = $DB->prepare("SELECT api2_application_user_token_information.*, user_in_api2_application_information.is_write_user_actions, ".
				" user_in_api2_application_information.is_write_user_profile, user_in_api2_application_information.is_write_calendar ".
				" FROM api2_application_user_token_information".
				" JOIN user_in_api2_application_information ON user_in_api2_application_information.is_in_app = '1' ".
				" AND user_in_api2_application_information.user_id = api2_application_user_token_information.user_id ".
				" AND user_in_api2_application_information.api2_application_id = api2_application_user_token_information.api2_application_id ".
				" WHERE api2_application_user_token_information.api2_application_id = :api2_application_id ".
				" AND api2_application_user_token_information.user_token = :user_token AND api2_application_user_token_information.user_secret = :user_secret");
		$stat->execute(array( 
				'api2_application_id'=>$app->getId(), 
				'user_token'=>$userToken,
				'user_secret'=>$userSecret,
			));
		if ($stat->rowCount() > 0) {
			$token = new API2ApplicationUserTokenModel();
			$token->setFromDataBaseRow($stat->fetch());
			return $token;
		}
	}
	
}


