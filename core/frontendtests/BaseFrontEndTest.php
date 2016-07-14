<?php

use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
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
class BaseFrontEndTest extends \PHPUnit_Framework_TestCase {


    /*
     * @var Silex\App
     */
    protected $app;

    protected $driver;

    /**
     * When we do an action that does NOT require loading something over the network before continuing, how long to wait?
     **/
    protected $sleepOnActionNoNetwork = 2;

    /**
     * When we do an action that requires loading something over the network before continuing, how long to wait?
     **/
    protected $sleepOnActionWithNetwork = 10;


    protected function setConfig(\Config $config) {

    }

    protected function setUp() {
        global $CONFIG, $DB, $EXTENSIONHOOKRUNNER, $app;


        $CONFIG = new \Config();
        require APP_ROOT_DIR."config.test.php";
        $CONFIG->isDebug = true;
        $this->setConfig($CONFIG);

        /** @var PDO * */
        if (!$DB)
        {
            $DB = new PDO('pgsql:host=' . $CONFIG->databaseHost . ';dbname=' . $CONFIG->databaseName, $CONFIG->databaseUser, $CONFIG->databasePassword);
            $DB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $DB->exec("SET CLIENT_ENCODING TO 'utf8'");
            $DB->exec("SET NAMES 'utf8'");
        }

        $this->app = new Silex\Application();
        $app = $this->app;
        $this->app['debug'] = true;
        $this->app['extensions'] = new ExtensionManager($this->app);
        foreach($CONFIG->extensions as $extensionName) {
            require APP_ROOT_DIR.'/extension/'.$extensionName.'/extension.php';
        }
        $this->app['appconfig'] = new appconfiguration\AppConfigurationManager($DB, $CONFIG);
        $this->app['config'] = $CONFIG;
        $this->app['db'] = $DB;
        $this->app['timesource'] = new TimeSource();
        $this->app['messagequeproducerhelper'] = function($app) { return new MessageQueProducerHelper($app); };
        $this->app['userAgent'] = new UserAgent();
        $this->app['extensionhookrunner'] = new ExtensionHookRunner($this->app);

        $EXTENSIONHOOKRUNNER = new ExtensionHookRunner($this->app);

        foreach($CONFIG->extensions as $extensionName) {
            $file = APP_ROOT_DIR . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR . $extensionName . DIRECTORY_SEPARATOR.'sql'.DIRECTORY_SEPARATOR.'destroy.sql';
            if (file_exists($file)) {
                foreach(explode(";", file_get_contents($file)) as $line) {
                    if (trim($line)) {
                        $DB->query($line.';');
                    }
                }
            }
        }
        foreach(explode(";", file_get_contents(__DIR__."/../sql/destroy.sql")) as $line) {
            if (trim($line)) {
                $DB->query($line.';');
            }
        }
        db\migrations\MigrationManager::upgrade($this->app, false);

        $host = 'http://localhost:4444/wd/hub';
        $this->driver = RemoteWebDriver::create($host, DesiredCapabilities::firefox());
        $this->driver->manage()->window()->maximize();

    }


    protected function getSiteQuotaUsedForTesting() {
        global $CONFIG;
        $siteQuotaRepository = new repositories\SiteQuotaRepository($this->app);
        return $siteQuotaRepository->loadByCode($CONFIG->newSiteHasQuotaCode);
    }

    protected function addCountriesToTestDB() {
        global $DB;
        $statInsert = $DB->prepare("INSERT INTO country (two_char_code,title,timezones,max_lat,max_lng,min_lat,min_lng) ".
            "VALUES (:two_char_code,:title,:timezones,:max_lat,:max_lng,:min_lat,:min_lng)");
        $statInsert->execute(array('two_char_code'=>'GB','title'=>'United Kingdom','timezones'=>'Europe/London','max_lat'=>null, 'max_lng'=>null, 'min_lat'=>null, 'min_lng'=>null));
        $statInsert->execute(array('two_char_code'=>'DE','title'=>'Germany','timezones'=>"Europe/Berlin,Europe/Busingen",'max_lat'=>null, 'max_lng'=>null, 'min_lat'=>null, 'min_lng'=>null));
    }


    protected function tearDown()
    {
        parent::tearDown();
        $this->driver->close();
    }



}


