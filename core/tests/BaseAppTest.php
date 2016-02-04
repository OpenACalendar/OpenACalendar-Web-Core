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
        $this->app['timesource'] = new TimeSource();
        $this->app['userAgent'] = new UserAgent();
        $this->app['extensionhookrunner'] = new ExtensionHookRunner($this->app);

        // These tests don't have a DB. But we put an entry in Pimple for it as some code relies on looking it up.
        // We put null in, so if anf code tries to use it it errors and we can change test to one with DB.
        $this->app['db'] = null;

        $EXTENSIONHOOKRUNNER = new ExtensionHookRunner($this->app);
	}


}


