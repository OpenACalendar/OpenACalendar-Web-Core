<?php

namespace indexapi2\controllers;

use Silex\Application;
use repositories\API2ApplicationRepository;
use repositories\API2ApplicationRequestTokenRepository;
use repositories\API2ApplicationUserAuthorisationTokenRepository;
use repositories\API2ApplicationUserTokenRepository;
use models\API2ApplicationUserPermissionsModel;
use models\API2ApplicationRequestTokenModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IndexController {
	
	function index(Application $app) {
		
		return "TODO";
		
	}
	
	function requestTokenJson(Application $app) {
		
		$data = array_merge($_GET, $_POST);
		
		$appRepo = new API2ApplicationRepository();
		
		// Load App
		$apiapp = $data['app_token'] && $data['app_secret'] ?
				$appRepo->loadByAppTokenAndAppSecret($data['app_token'], $data['app_secret'])
				: null;
		
		// Is correct?
		if (!$apiapp) {
			// TODO also if app closed
			return json_encode(array(
				'success'=>false,
			));
		}
		
		// Settings
		$requestToken = new \models\API2ApplicationRequestTokenModel();
		if ($apiapp->getIsCallbackUrl() && isset($data['callback_url']) && trim($data['callback_url'])) {
			if ($apiapp->isCallbackUrlAllowed(trim($data['callback_url']))) {
				$requestToken->setCallbackUrl(trim($data['callback_url']));
			} else {
				return json_encode(array(
					'success'=>false,
					'error_message'=>'That callback URL is not allowed',
				));
			}
		}
		if ($apiapp->getIsCallbackDisplay() && isset($data['callback_display']) && strtolower(trim($data['callback_display'])) == "true") {
			$requestToken->setIsCallbackDisplay(true);
		}
		if ($apiapp->getIsCallbackJavascript() && isset($data['callback_javascript']) && strtolower(trim($data['callback_javascript'])) == "true") {
			$requestToken->setIsCallbackJavascript(true);
		}
		// $requestToken->setUserId();  TODO
		
		$scopeArray = isset($data['scope']) ? explode(",", str_replace(" ", ",", $data['scope'])) : array();
		$requestToken->setIsWriteUserActions(in_array('permission_write_user_actions', $scopeArray) && $apiapp->getIsWriteUserActions());
		$requestToken->setIsWriteUserProfile(in_array('permission_write_user_profile', $scopeArray) && $apiapp->getIsWriteUserProfile());
		$requestToken->setIsWriteCalendar(in_array('permission_write_calendar', $scopeArray) && $apiapp->getIsWriteCalendar());
		
		$requestToken->setStateFromUser(isset($data['state']) ? $data['state'] : null);
		
		// Check 
		if (!$requestToken->isAnyCallbackSet()) {
			return json_encode(array(
				'success'=>false,
				'error_message'=>'You must pass a callback',
			));
		}
		
		// Generate Token
		$tokenRepo = new API2ApplicationRequestTokenRepository();
		$token = $tokenRepo->create($apiapp, $requestToken);
		
		return json_encode(array(
			'success'=>true,
			'request_token'=>$token->getRequestToken(),
		));
	}
	
	function login(Application $app) {
		
		$appRepo = new API2ApplicationRepository();
		$appRequestTokenRepo = new API2ApplicationRequestTokenRepository();
		$userAuthorisationTokenRepo =  new API2ApplicationUserAuthorisationTokenRepository();
		
		######################################## Check Data In
		
		// Load and check request token!
		$data = array_merge($_GET, $_POST);
		$apiapp = $data['app_token'] ? $appRepo->loadByAppToken($data['app_token']) : null;
		if (!$apiapp) {
			// TODO also if app closed
			return $app['twig']->render('indexapi2/index/login.app.problem.html.twig', array());
		}
		$requestToken = $data['request_token'] ? $appRequestTokenRepo->loadByAppAndRequestToken($apiapp, $data['request_token']) : null;
		if (!$requestToken) {
			// TODO also if token already used
			return $app['twig']->render('indexapi2/index/login.requestToken.problem.html.twig', array());
		}
		$userAuthorisationToken = null;
		$permissionsGranted = new API2ApplicationUserPermissionsModel();
		
		######################################## User Workflow
		
		if (!userGetCurrent()) {
			
			// TODO Show Login Or Create Screen
			
		}
		
		if ($apiapp->getIsAutoApprove()) {

			// Let's go!
			$permissionsGranted->setFromApp($apiapp);
			$userAuthorisationToken = $userAuthorisationTokenRepo->createForAppAndUserFromRequestToken($apiapp, userGetCurrent(), $requestToken, $permissionsGranted);
			
			
		} else {

			// TODO if app already approved, let's go

			// TODO Show Approval Screen

		}
		
		###################################### Return
		
		
		if ($requestToken->getCallbackUrl()) {
			if ($userAuthorisationToken) {
				return $app->redirect($requestToken->getCallbackUrlWithParams(array(
						'authorisation_token'=>$userAuthorisationToken->getAuthorisationToken(),
						'state'=>$requestToken->getStateFromUser(),
					)));
			} else {
				return $app->redirect($requestToken->getCallbackUrlWithParams(array(
						'status'=>'failure',
					)));
			}
		} else if ($requestToken->getIsCallbackJavascript()) {
			if ($userAuthorisationToken) {
				// TODO
			} else {
				// TODO
			}
		} else if ($requestToken->getIsCallbackDisplay()) {
			if ($userAuthorisationToken) {
				return $app['twig']->render('indexapi2/index/login.callback.display.success.html.twig', array(
						'authorisationToken'=>$userAuthorisationToken->getAuthorisationToken(),
					));
			} else {
				return $app['twig']->render('indexapi2/index/login.callback.display.failure.html.twig', array(
					));
			}
		} else {
			return "No Callback was given!";
		}
		
		
		return "???";
	}
	
	public function userTokenJson(Application $app) {
		$appRepo = new API2ApplicationRepository();
		$appRequestTokenRepo = new API2ApplicationRequestTokenRepository();
		$userAuthorisationTokenRepo =  new API2ApplicationUserAuthorisationTokenRepository();
		$userTokenRepo = new API2ApplicationUserTokenRepository();
		
		// Load and check request token!
		$data = array_merge($_GET, $_POST);
		$apiapp = $data['app_token'] && $data['app_secret'] ?
				$appRepo->loadByAppTokenAndAppSecret($data['app_token'], $data['app_secret'])
				: null;
		if (!$apiapp) {
			// TODO also if app closed
			return json_encode(array(
				'success'=>false,
			));
		}
		$authorisationToken = $data['authorisation_token'] && $data['request_token'] ?
				$userAuthorisationTokenRepo->loadByAppAndAuthorisationTokenAndRequestToken($apiapp, $data['authorisation_token'], $data['request_token'])
				: null;
		if (!$authorisationToken) {
			// TODO also if token already used
			return json_encode(array(
					'success'=>false,
				));
		}
		
		// get user tokens
		$userToken = $userTokenRepo->loadByAppAndUserID($apiapp, $authorisationToken->getUserId());
		// TODO add permissions in here to
		return json_encode(array(
				'success'=>true,
				'user_token'=>$userToken->getUserToken(),
				'user_secret'=>$userToken->getUserSecret(),
			));
		
	}
	
	
	public function currentUserJson(Application $app) {

		return json_encode(array(
				'success'=>true,
				'user'=>array(
					'username'=>$app['apiUser']->getUserName(),
				),
				'permissions'=>array(
					'is_write_user_actions'=>$app['apiUserToken']->getIsWriteUserActions(),
					'is_write_user_profile'=>$app['apiUserToken']->getIsWriteUserProfile(),
					'is_write_calendar'=>$app['apiUserToken']->getIsWriteCalendar(),
				)
			));
	}
	
	
}


