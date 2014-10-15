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

		if (!$app['apiApp'] || !$app['apiAppLoadedBySecret']) {
			return json_encode(array(
				'success'=>false,
			));
		}

		
		// Settings
		$requestToken = new \models\API2ApplicationRequestTokenModel();
		if ($app['apiApp']->getIsCallbackUrl() && isset($data['callback_url']) && trim($data['callback_url'])) {
			if ($app['apiApp']->isCallbackUrlAllowed(trim($data['callback_url']))) {
				$requestToken->setCallbackUrl(trim($data['callback_url']));
			} else {
				return json_encode(array(
					'success'=>false,
					'error_message'=>'That callback URL is not allowed',
				));
			}
		}
		if ($app['apiApp']->getIsCallbackDisplay() && isset($data['callback_display']) && strtolower(trim($data['callback_display'])) == "true") {
			$requestToken->setIsCallbackDisplay(true);
		}
		if ($app['apiApp']->getIsCallbackJavascript() && isset($data['callback_javascript']) && strtolower(trim($data['callback_javascript'])) == "true") {
			$requestToken->setIsCallbackJavascript(true);
		}
		// $requestToken->setUserId();  TODO
		
		$scopeArray = isset($data['scope']) ? explode(",", str_replace(" ", ",", $data['scope'])) : array();
		$requestToken->setIsEditor(in_array('permission_editor', $scopeArray) && $app['apiApp']->getIsEditor());

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
		$token = $tokenRepo->create($app['apiApp'], $requestToken);
		
		return json_encode(array(
			'success'=>true,
			'request_token'=>$token->getRequestToken(),
		));
	}
	
	function login(Request $request, Application $app) {
		if (!$app['apiApp']) {
			return $app['twig']->render('indexapi2/index/login.app.problem.html.twig', array());
		}

		$appRequestTokenRepo = new API2ApplicationRequestTokenRepository();
		$userAuthorisationTokenRepo =  new API2ApplicationUserAuthorisationTokenRepository();
		$userInApp2Repo = new UserInAPI2ApplicationRepository();
		
		######################################## Check Data In
		
		// Load and check request token!
		$data = array();
		if ($app['websession']->has('api2requestToken')) $data['request_token'] = $app['websession']->get('api2requestToken');
		$data = array_merge($data, $_GET, $_POST);

		$requestToken = $data['request_token'] ? $appRequestTokenRepo->loadByAppAndRequestToken($app['apiApp'], $data['request_token']) : null;
		if (!$requestToken || $requestToken->getIsUsed()) {
			return $app['twig']->render('indexapi2/index/login.requestToken.problem.html.twig', array());
		}
		$userAuthorisationToken = null;
		$permissionsGranted = new API2ApplicationUserPermissionsModel();

		$app['websession']->set('api2appToken', $app['apiApp']->getAppToken());
		$app['websession']->set('api2requestToken', $requestToken->getRequestToken());

		
		######################################## User Workflow
		
		$formObj = new LogInUserForm(userGetCurrent(), $app['apiApp'], $requestToken);
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
						
						if ($app['apiApp']->getIsAutoApprove()) {
							$permissionsGranted->setFromApp($app['apiApp']);
						} else {
							$permissionsGranted->setFromData($formData);
						}
						$userInApp2Repo->setPermissionsForUserInApp($permissionsGranted, $user, $app['apiApp']);
						$userAuthorisationToken = $userAuthorisationTokenRepo->createForAppAndUserFromRequestToken($app['apiApp'], $user, $requestToken);
						
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
						'api2app'=>$app['apiApp'],
						'askForPermissionEditor' => $formObj->getIsEditor(),
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

		if (!$app['apiApp'] || !$app['apiAppLoadedBySecret']) {
			return json_encode(array(
				'success'=>false,
			));
		}

		// Load and check request token!
		$data = array_merge($_GET, $_POST);

		$authorisationToken = $data['authorisation_token'] && $data['request_token'] ?
				$userAuthorisationTokenRepo->loadByAppAndAuthorisationTokenAndRequestToken($app['apiApp'], $data['authorisation_token'], $data['request_token'])
				: null;
		if (!$authorisationToken || $authorisationToken->getIsUsed()) {
			return json_encode(array(
					'success'=>false,
				));
		}
		
		// get user tokens
		$userTokenRepo->createForAppAndUserId($app['apiApp'], $authorisationToken->getUserId());
		$userToken = $userTokenRepo->loadByAppAndUserID($app['apiApp'], $authorisationToken->getUserId());
		// mark token used
		$userAuthorisationTokenRepo->markTokenUsed($authorisationToken);
		// return
		if ($userToken) {
			return json_encode(array(
					'success'=>true,
					'permissions'=>array(
						'is_editor'=>$userToken->getIsEditor(),
					),
					'user_token'=>$userToken->getUserToken(),
					'user_secret'=>$userToken->getUserSecret(),
				));
		} else {
			// This might happen if user redraws permissions from app between logging in and app gotting tokens, 
			//   since loadByAppAndUserID() checks user permisisons.
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
					'is_editor'=>$app['apiUserToken']->getIsEditor(),
				)
			));
	}
	
	
}


