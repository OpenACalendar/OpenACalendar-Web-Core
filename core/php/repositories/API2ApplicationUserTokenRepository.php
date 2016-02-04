<?php

namespace repositories;

use models\API2ApplicationModel;
use models\API2ApplicationUserTokenModel;
use models\UserAccountModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class API2ApplicationUserTokenRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

	
	public function createForAppAndUserId(API2ApplicationModel $app, $userID) {

		$stat = $this->app['db']->prepare("SELECT api2_application_user_token_information.* FROM api2_application_user_token_information WHERE ".
				"api2_application_id =:api2_application_id AND user_id =:user_id");
		$stat->execute(array( 'api2_application_id'=>$app->getId(), 'user_id'=>$userID ));
		if ($stat->rowCount() == 0) {
			
			$stat = $this->app['db']->prepare("INSERT INTO api2_application_user_token_information ".
					"(api2_application_id, user_id, user_token, user_secret, created_at) ".
					"VALUES (:api2_application_id, :user_id, :user_token, :user_secret, :created_at)");
			
			$stat->execute(array(
				'api2_application_id'=>$app->getId(),
				'user_id'=> $userID ,
				'user_token'=> createKey(1,255),
				'user_secret'=> createKey(1,255),
				'created_at'=>  $this->app['timesource']->getFormattedForDataBase(),
			));
					
			// TODO check for unique user_token
			
		}
		
	}
	
	
	/** 
	 * 
	 * @return \models\API2ApplicationUserTokenModel
	 */
	public function loadByAppAndUserID(API2ApplicationModel $app, $userID) {
		$stat = $this->app['db']->prepare("SELECT api2_application_user_token_information.*, user_in_api2_application_information.is_editor ".
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
		$stat = $this->app['db']->prepare("SELECT api2_application_user_token_information.*, user_in_api2_application_information.is_editor ".
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


