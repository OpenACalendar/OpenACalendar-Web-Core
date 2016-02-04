<?php


namespace repositories;

use models\SendEmailModel;
use models\SiteModel;
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
class SendEmailRepository {

    /** @var Application */
    private  $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }


    public function create(SendEmailModel $sendEmail, SiteModel $site, UserAccountModel $creator) {

		try {
			$this->app['db']->beginTransaction();

			$stat = $this->app['db']->prepare("SELECT max(slug) AS c FROM send_email_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$sendEmail->setSlug($data['c'] + 1);
			
			$stat = $this->app['db']->prepare("INSERT INTO send_email_information (site_id, slug, subject,send_to,introduction,event_html,event_text,days_into_future,created_at,created_by,timezone) ".
					"VALUES (:site_id, :slug, :subject,:send_to,:introduction,:event_html,:event_text,:days_into_future,:created_at,:created_by,:timezone) RETURNING id");
			$stat->execute(array(
					'site_id'=>$site->getId(), 
					'slug'=>$sendEmail->getSlug(),
					'subject'=>substr($sendEmail->getSubject(),0,VARCHAR_COLUMN_LENGTH_USED),
					'send_to'=>$sendEmail->getSendTo(),
					'introduction'=>$sendEmail->getIntroduction(),
					'event_html'=>$sendEmail->getEventHTML(),
					'event_text'=>$sendEmail->getEventText(),
					'days_into_future'=>$sendEmail->getDaysIntoFuture(),
					'timezone'=>$sendEmail->getTimezone(),
					'created_by'=>$creator->getId(),
					'created_at'=>$this->app['timesource']->getFormattedForDataBase()
				));
			$data = $stat->fetch();
			$sendEmail->setId($data['id']);
			
			$stat = $this->app['db']->prepare("INSERT INTO send_email_has_event (send_email_id,event_id) VALUES (:send_email_id,:event_id) ");
			foreach($sendEmail->getEvents() as $event) {
				$stat->execute(array('send_email_id'=>$sendEmail->getId(),'event_id'=>$event->getId()));
			}
			
			$this->app['db']->commit();
		} catch (Exception $e) {
			$this->app['db']->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {

		$stat = $this->app['db']->prepare("SELECT send_email_information.* FROM send_email_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$sem = new SendEmailModel();
			$sem->setFromDataBaseRow($stat->fetch());
			return $sem;
		}
	}
	
	public function markSent(SendEmailModel $sendEmail, UserAccountModel $sentBy) {

		$stat = $this->app['db']->prepare("UPDATE send_email_information SET sent_at=:sent_at, sent_by=:sent_by WHERE id =:id");
		$stat->execute(array( 
				'id'=>$sendEmail->getId(), 
				'sent_at'=>  $this->app['timesource']->getFormattedForDataBase(),
				'sent_by'=>$sentBy->getId(), 
			));
	}
	
}

