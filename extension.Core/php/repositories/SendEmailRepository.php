<?php


namespace repositories;

use models\SendEmailModel;
use models\SiteModel;
use models\UserAccountModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendEmailRepository {
	
	
	public function create(SendEmailModel $sendEmail, SiteModel $site, UserAccountModel $creator) {
		global $DB;
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT max(slug) AS c FROM send_email_information WHERE site_id=:site_id");
			$stat->execute(array('site_id'=>$site->getId()));
			$data = $stat->fetch();
			$sendEmail->setSlug($data['c'] + 1);
			
			$stat = $DB->prepare("INSERT INTO send_email_information (site_id, slug, subject,send_to,introduction,event_html,event_text,days_into_future,created_at,created_by,timezone) ".
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
					'created_at'=>\TimeSource::getFormattedForDataBase()
				));
			$data = $stat->fetch();
			$sendEmail->setId($data['id']);
			
			$stat = $DB->prepare("INSERT INTO send_email_has_event (send_email_id,event_id) VALUES (:send_email_id,:event_id) ");
			foreach($sendEmail->getEvents() as $event) {
				$stat->execute(array('send_email_id'=>$sendEmail->getId(),'event_id'=>$event->getId()));
			}
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
	public function loadBySlug(SiteModel $site, $slug) {
		global $DB;
		$stat = $DB->prepare("SELECT send_email_information.* FROM send_email_information WHERE slug =:slug AND site_id =:sid");
		$stat->execute(array( 'sid'=>$site->getId(), 'slug'=>$slug ));
		if ($stat->rowCount() > 0) {
			$sem = new SendEmailModel();
			$sem->setFromDataBaseRow($stat->fetch());
			return $sem;
		}
	}
	
	public function markSent(SendEmailModel $sendEmail, UserAccountModel $sentBy) {
		global $DB;
		$stat = $DB->prepare("UPDATE send_email_information SET sent_at=:sent_at, sent_by=:sent_by WHERE id =:id");
		$stat->execute(array( 
				'id'=>$sendEmail->getId(), 
				'sent_at'=>  \TimeSource::getFormattedForDataBase(), 
				'sent_by'=>$sentBy->getId(), 
			));
	}
	
}

