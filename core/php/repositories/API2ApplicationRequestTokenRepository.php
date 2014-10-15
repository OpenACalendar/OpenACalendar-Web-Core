<?php

namespace repositories;

use models\API2ApplicationModel;
use models\API2ApplicationRequestTokenModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationRequestTokenRepository {

	/**
	 * 
	 * @return \models\API2ApplicationRequestTokenModel
	 */
	public function create(API2ApplicationModel $app, API2ApplicationRequestTokenModel $requestToken) {
		global $DB;
		
		$requestToken->setRequestToken(createKey(1,255));
		
		// TODO make sure token is unique!!!!!
		
		$stat = $DB->prepare("INSERT INTO api2_application_request_token (api2_application_id, request_token, created_at, user_id, ".
				"callback_url, is_callback_display, is_callback_javascript, is_editor, state_from_user) ".
				"VALUES (:api2_application_id, :request_token, :created_at,  :user_id, :callback_url, ".
				":is_callback_display, :is_callback_javascript, :is_editor, :state_from_user)");
		$stat->execute(array( 
				'api2_application_id'=>$app->getId(), 
				'request_token'=>$requestToken->getRequestToken() ,
				'created_at'=>  \TimeSource::getFormattedForDataBase(),
				'user_id'=>  null, // TODO
				'callback_url'=>$app->getIsCallbackUrl() ? $requestToken->getCallbackUrl() : null,
				'is_callback_display'=>($app->getIsCallbackDisplay() && $requestToken->getIsCallbackDisplay())?1:0,
				'is_callback_javascript'=>($app->getIsCallbackJavascript() && $requestToken->getIsCallbackJavascript())?1:0,
				'is_editor'=>($requestToken->getIsEditor() && $app->getIsEditor())?1:0,
				'state_from_user'=>$requestToken->getStateFromUser(),
			));
		
		return $requestToken;
	}
	
	
	/** 
	 * 
	 * @return \models\API2ApplicationModel
	 */
	public function loadByAppAndRequestToken(API2ApplicationModel $app, $requestToken) {
		global $DB;
		$stat = $DB->prepare("SELECT api2_application_request_token.* FROM api2_application_request_token".
				" WHERE api2_application_id = :api2_application_id AND request_token = :request_token");
		$stat->execute(array( 'api2_application_id'=>$app->getId(), 'request_token'=>$requestToken ));
		if ($stat->rowCount() > 0) {
			$token = new API2ApplicationRequestTokenModel();
			$token->setFromDataBaseRow($stat->fetch());
			return $token;
		}
	}
	
}

