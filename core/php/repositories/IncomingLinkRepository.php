<?php


namespace repositories;
use models\SiteModel;
use Silex\Application;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class IncomingLinkRepository {


    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

	public function create(\BaseIncomingLink $incomingLink, SiteModel $site=null) {

		try {
			$this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("INSERT INTO incoming_link (site_id, extension_id, type, source_url, target_url, reporter_useragent, reporter_ip, created_at) ".
					"VALUES (:site_id, :extension_id, :type, :source_url, :target_url, :reporter_useragent, :reporter_ip, :created_at) RETURNING id");
			$stat->execute(array(
					'site_id'=>($site ? $site->getId() : null),
					'extension_id'=>$incomingLink->getTypeExtensionID(),
					'type'=>$incomingLink->getType(),
					'source_url'=>$incomingLink->getSourceURL(),
					'target_url'=>$incomingLink->getTargetURL(),
					'reporter_useragent'=>$incomingLink->getReporterUseragent(),
					'reporter_ip'=>$incomingLink->getReporterIp(),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase(),
				));
			$data = $stat->fetch();
			$incomingLink->setId($data['id']);

			$this->app['db']->commit();
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}


}
