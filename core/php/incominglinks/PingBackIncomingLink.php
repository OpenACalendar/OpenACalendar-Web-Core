<?php
namespace incominglinks;
use models\SiteModel;
use pingback\ParsePingBack;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class PingBackIncomingLink extends \BaseIncomingLink {



	public static function  receive(Application $app, SiteModel $siteModel = null) {


		$data = file_get_contents('php://input');

		$app['monolog']->addError("receivepingback.php got data ".$data);

		$pingback = ParsePingBack::parseFromData($data);

		if ($pingback) {

			$pbil = new \incominglinks\PingBackIncomingLink();
			$pbil->setSourceURL($pingback->getSourceUrl());
			$pbil->setTargetURL($pingback->getTargetUrl());
			$pbil->setReporterIp($_SERVER['REMOTE_ADDR']);
			$pbil->setReporterUseragent($_SERVER['HTTP_USER_AGENT']);

			$repo = new \repositories\IncomingLinkRepository($app);
			$repo->create($pbil, $siteModel);

			print '<?xml version="1.0" encoding="ISO-8859-1"?>
<methodResponse>
   <params>
      <param>
         <value><string>Reported</string></value>
      </param>
   </params>
</methodResponse>';

		} else {
			// TODO
		}

	}

	public function getType()
	{
		return "PINGBACK";
	}






}
