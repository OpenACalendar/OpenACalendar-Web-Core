<?php

namespace indexapi2\controllers;

use Silex\Application;
use repositories\API2ApplicationRepository;
use repositories\API2ApplicationRequestTokenRepository;
use repositories\API2ApplicationUserAuthorisationTokenRepository;
use repositories\API2ApplicationUserTokenRepository;
use repositories\UserInAPI2ApplicationRepository;
use repositories\UserAccountRepository;
use models\API2ApplicationUserPermissionsModel;
use models\API2ApplicationRequestTokenModel;
use Symfony\Component\HttpFoundation\Request;
use indexapi2\forms\LogInUserForm;

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
	
	function login(Request $request, Application $app) {
		global $WEBSESSION;
		
		$appRepo = new API2ApplicationRepository();
		$appRequestTokenRepo = new API2ApplicationRequestTokenRepository();
		$userAuthorisationTokenRepo =  new API2ApplicationUserAuthorisationTokenRepository();
		$userInApp2Repo = new UserInAPI2ApplicationRepository();
		
		######################################## Check Data In
		
		// Load and check request token!
		$data = array();
		if ($WEBSESSION->has('api2appToken')) $data['app_token'] = $WEBSESSION->get('api2appToken');
		if ($WEBSESSION->has('api2requestToken')) $data['request_token'] = $WEBSESSION->get('api2requestToken');
		$data = array_merge($data, $_GET, $_POST);
		
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

		$WEBSESSION->set('api2appToken', $apiapp->getAppToken());
		$WEBSESSION->set('api2requestToken', $requestToken->getRequestToken());

		
		######################################## User Workflow
		
		$formObj = new LogInUserForm(userGetCurrent(), $apiapp, $requestToken);
		$form = $app['form.factory']->create($formObj);
		
		if ('POST' == $request->getMethod()) {
			$form->bind($request);

			if ($form->isValid()) {
				$formData = $form->getData();
				
				$userRepository = new UserAccountRepository();
				if ($formData['email']) {
					$user = $userRepository->loadByEmail($formData['email']);
				} else if ($formData['username']) {
					$user = $userRepository->loadByUserName($formData['username']);
				}
				if ($user) {
					if ($user->checkPassword($formData['password'])) {
						
						if ($apiapp->getIsAutoApprove()) {
							$permissionsGranted->setFromApp($apiapp);
						} else {
							$permissionsGranted->setFromData($formData);
						}
						$userInApp2Repo->setPermissionsForUserInApp($permissionsGranted, $user, $apiapp);
						$userAuthorisationToken = $userAuthorisationTokenRepo->createForAppAndUserFromRequestToken($apiapp, $user, $requestToken);
						
					} else {
						$app['monolog']->addError("Login attempt on API2 - account ".$user->getId().' - password wrong.');
						$form->addError(new FormError('User and password not recognised'));
					}
				} else {
					$app['monolog']->addError("Login attempt on API2 - unknown account");
					$form->addError(new FormError('User and password not recognised'));
				}
				
			}
		}
		
		if (!$userAuthorisationToken) {
			return $app['twig']->render('indexapi2/index/login.html.twig', array(
						'form'=>$form->createView(),
						'api2app'=>$apiapp,
						'askForPermissionWriteUserActions' => $formObj->getIsWriteUserActions(),
						'askForPermissionWriteCalendar' => $formObj->getIsWriteCalendar(),
					));
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
				return $app['twig']->render('indexapi2/index/login.callback.javascript.success.html.twig', array(
						'authorisationToken'=>$userAuthorisationToken->getAuthorisationToken(),
						'state'=>$requestToken->getStateFromUser(),
					));
			} else {
				return $app['twig']->render('indexapi2/index/login.callback.javascript.failure.html.twig', array(
					));
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
		$userTokenRepo->createForAppAndUserId($apiapp, $authorisationToken->getUserId());
		$userToken = $userTokenRepo->loadByAppAndUserID($apiapp, $authorisationToken->getUserId());
		if ($userToken) {
			return json_encode(array(
					'success'=>true,
					'permissions'=>array(
						'is_write_user_actions'=>$userToken->getIsWriteUserActions(),
						'is_write_calendar'=>$userToken->getIsWriteCalendar(),
					),
					'user_token'=>$userToken->getUserToken(),
					'user_secret'=>$userToken->getUserSecret(),
				));
		} else {
			return json_encode(array(
					'success'=>false,
				));
		}
		
	}
	
	
	public function currentUserJson(Application $app) {

		return json_encode(array(
				'success'=>true,
				'user'=>array(
					'username'=>$app['apiUser']->getUserName(),
				),
				'permissions'=>array(
					'is_write_user_actions'=>$app['apiUserToken']->getIsWriteUserActions(),
					'is_write_calendar'=>$app['apiUserToken']->getIsWriteCalendar(),
				)
			));
	}
	
	
}


