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
			API2ApplicationRequestTokenModel $requestToken) {
		global $DB;
		
		$token = new \models\API2ApplicationUserAuthorisationTokenModel();
		$token->setApi2ApplicationId($app->getId());
		$token->setUserId($user->getId());
		$token->setRequestToken($requestToken->getRequestToken());
		$token->setAuthorisationToken(createKey(1,255));
		
		global $DB;
		try {
			$DB->beginTransaction();
		
			// Mark Request Token used
			$stat = $DB->prepare("UPDATE api2_application_request_token SET used_at=:used_at ".
					"WHERE api2_application_id=:api2_application_id AND request_token=:request_token");
			$stat->execute(array(
				'used_at'=>\TimeSource::getFormattedForDataBase(),
				'api2_application_id'=>$app->getId(),
				'request_token'=>$requestToken->getRequestToken(),
			));

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
		
		
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
		
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


