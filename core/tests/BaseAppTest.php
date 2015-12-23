<?php

use models\UserAccountModel;
use models\SiteModel;
use models\GroupModel;
use models\EventModel;
use repositories\UserAccountRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\EventRepository;
use repositories\builders\EventRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class BaseAppTest extends \PHPUnit_Framework_TestCase {

	/*
	 * @var Silex\App
	 */
	protected $app;


    protected function setUp() {
		global $CONFIG, $DB, $EXTENSIONHOOKRUNNER, $app;

		$CONFIG = new \Config();
		require APP_ROOT_DIR."config.test.php";
		$CONFIG->isDebug = true;

		$EXTENSIONHOOKRUNNER = new ExtensionHookRunner();

		$this->app = new Silex\Application();
		$app = $this->app;
		$this->app['debug'] = true;
		$this->app['extensions'] = new ExtensionManager($this->app);
		foreach($CONFIG->extensions as $extensionName) {
			require APP_ROOT_DIR.'/extension/'.$extensionName.'/extension.php';
		}
		$this->app['appconfig'] = new appconfiguration\AppConfigurationManager($DB, $CONFIG);
		$this->app['config'] = $CONFIG;
        $this->app['messagequeproducerhelper'] = function($app) { return new MessageQueProducerHelper($app); };

	}


}


