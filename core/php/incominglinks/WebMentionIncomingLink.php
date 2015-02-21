<?php
namespace incominglinks;
use models\SiteModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class WebMentionIncomingLink extends \BaseIncomingLink {




	public static function  receive(Application $app, SiteModel $siteModel = null) {

		$data = array_merge($_POST, $_GET);

		if (isset($data['source']) && isset($data['target'])) {

			$pbil = new \incominglinks\WebMentionIncomingLink();
			$pbil->setSourceURL($data['source']);
			$pbil->setTargetURL($data['target']);
			$pbil->setReporterIp($_SERVER['REMOTE_ADDR']);
			$pbil->setReporterUseragent($_SERVER['HTTP_USER_AGENT']);

			$repo = new \repositories\IncomingLinkRepository();
			$repo->create($pbil, $siteModel);

			header("HTTP/1.0 202 ");

			print "WebMention Received";

		} else {

			// TODO

		}
	}






	public function getType()
	{
		return "WEBMENTION";
	}


}
