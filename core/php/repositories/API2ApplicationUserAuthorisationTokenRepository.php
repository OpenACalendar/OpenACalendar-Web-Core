<?php

namespace repositories;

use models\API2ApplicationModel;
use models\API2ApplicationRequestTokenModel;
use models\UserAccountModel;
use models\API2ApplicationUserAuthorisationTokenModel;
use models\API2ApplicationUserPermissionsModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationUserAuthorisationTokenRepository {

	
	public function createForAppAndUserFromRequestToken(API2ApplicationModel $app, UserAccountModel $user, 
			API2ApplicationRequestTokenModel $requestToken, API2ApplicationUserPermissionsModel $permissions) {
		global $DB;
		
		// ############# First, can we load user in app? if not create one
		$stat = $DB->prepare("SELECT user_in_api2_application_information.* FROM user_in_api2_application_information WHERE ".
				"api2_application_id =:api2_application_id AND user_id =:user_id");
		$stat->execute(array( 'api2_application_id'=>$app->getId(), 'user_id'=>$user->getId() ));
		if ($stat->rowCount() == 0) {
			$stat = $DB->prepare("INSERT INTO user_in_api2_application_information ".
					"(api2_application_id, user_id, is_write_user_actions, is_write_user_profile, is_write_calendar, created_at) ".
					"VALUES (:api2_application_id, :user_id, :is_write_user_actions, :is_write_user_profile, :is_write_calendar, :created_at)");
			
			$stat->execute(array(
				'api2_application_id'=>$app->getId(),
				'user_id'=> $user->getId() ,
				'is_write_user_actions'=>  $permissions->getIsWriteUserActions() ? 1 : 0,
				'is_write_user_profile'=>  $permissions->getIsWriteUserProfile() ? 1 : 0,
				'is_write_calendar'=> $permissions->getIsWriteCalendar() ? 1 : 0 ,
				'created_at'=>  \TimeSource::getFormattedForDataBase(),
			));
					
			
		} else {
			
			// TODO get data, check if we need to escalate permissions
			
		}
		
		// ############# Second, can we load a User Token? If not create one.
		$stat = $DB->prepare("SELECT api2_application_user_token_information.* FROM api2_application_user_token_information WHERE ".
				"api2_application_id =:api2_application_id AND user_id =:user_id");
		$stat->execute(array( 'api2_application_id'=>$app->getId(), 'user_id'=>$user->getId() ));
		if ($stat->rowCount() == 0) {
			$stat = $DB->prepare("INSERT INTO api2_application_user_token_information ".
					"(api2_application_id, user_id, user_token, user_secret, created_at) ".
					"VALUES (:api2_application_id, :user_id, :user_token, :user_secret, :created_at)");
			
			$stat->execute(array(
				'api2_application_id'=>$app->getId(),
				'user_id'=> $user->getId() ,
				'user_token'=> createKey(1,255),
				'user_secret'=> createKey(1,255),
				'created_at'=>  \TimeSource::getFormattedForDataBase(),
			));
					
			// TODO check for unique user_token
			
		}
		
		// ############# TODO Mark Request Token used
		
		// ############# Now create a user access token.
		
		$token = new \models\API2ApplicationUserAuthorisationTokenModel();
		$token->setApi2ApplicationId($app->getId());
		$token->setUserId($user->getId());
		$token->setRequestToken($requestToken->getRequestToken());
		$token->setAuthorisationToken(createKey(1,255));
		
		// TODO make sure token is unique!!!!!
		
		$stat = $DB->prepare("INSERT INTO api2_application_user_authorisation_token (api2_application_id, user_id, authorisation_token, request_token, created_at) ".
				"VALUES (:api2_application_id, :user_id, :authorisation_token,:request_token, :created_at)");
		$stat->execute(array( 
			'api2_application_id'=>$app->getId(), 
			'user_id'=>$user->getId(),
			'authorisation_token'=>$token->getAuthorisationToken() ,
			'request_token'=>$token->getRequestToken() ,
			'created_at'=>  \TimeSource::getFormattedForDataBase()
				));
		
		return $token;
		
	}
	
		
	/** 
	 * 
	 * @return \models\API2ApplicationUserAuthorisationTokenModel
	 */
	public function loadByAppAndAuthorisationTokenAndRequestToken(API2ApplicationModel $app, $authorisationToken, $requestToken) {
		global $DB;
		$stat = $DB->prepare("SELECT api2_application_user_authorisation_token.* FROM api2_application_user_authorisation_token ".
				" WHERE api2_application_id = :api2_application_id AND authorisation_token = :authorisation_token AND request_token = :request_token");
		$stat->execute(array( 'api2_application_id'=>$app->getId(), 'authorisation_token'=>$authorisationToken, 'request_token'=>$requestToken ));
		if ($stat->rowCount() > 0) {
			$token = new API2ApplicationUserAuthorisationTokenModel();
			$token->setFromDataBaseRow($stat->fetch());
			return $token;
		}
	}
	
	
}


