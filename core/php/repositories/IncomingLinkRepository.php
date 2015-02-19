<?php


namespace repositories;
use models\SiteModel;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IncomingLinkRepository {

	function __construct()
	{
	}


	public function create(\BaseIncomingLink $incomingLink, SiteModel $site=null) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("INSERT INTO incoming_link (site_id, extension_id, type, source_url, target_url, reporter_useragent, reporter_ip, created_at) ".
					"VALUES (:site_id, :extension_id, :type, :source_url, :target_url, :reporter_useragent, :reporter_ip, :created_at) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(),
					'extension_id'=>$incomingLink->getTypeExtensionID(),
					'type'=>$incomingLink->getType(),
					'source_url'=>$incomingLink->getSourceURL(),
					'target_url'=>$incomingLink->getTargetURL(),
					'reporter_useragent'=>$incomingLink->getReporterUseragent(),
					'reporter_ip'=>$incomingLink->getReporterIp(),
					'created_at'=>\TimeSource::getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$incomingLink->setId($data['id']);

			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}


}
