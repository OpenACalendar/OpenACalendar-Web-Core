<?php


namespace dbaccess;

use models\UserAccountModel;
use models\EventModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class EventDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;


	function __construct($db, $timesource)
	{
		$this->db = $db;
		$this->timesource = $timesource;
	}


	public function update(EventModel $event, $fields, UserAccountModel $user = null, EventHistoryModel $fromHistory = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}


			$stat = $this->db->prepare("UPDATE event_information  SET summary=:summary, description=:description, ".
				"start_at=:start_at, end_at=:end_at, is_deleted=:is_deleted, area_id=:area_id, ".
				" venue_id=:venue_id, country_id=:country_id, timezone=:timezone, ".
				"url=:url, ticket_url=:ticket_url, is_physical=:is_physical, is_virtual=:is_virtual ".
				"WHERE id=:id");
			$stat->execute(array(
				'id'=>$event->getId(),
				'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$event->getDescription(),
				'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
				'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
				'venue_id'=>$event->getVenueId(),
				'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
				'country_id'=>$event->getCountryId(),
				'timezone'=>$event->getTimezone(),
				'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
				'ticket_url'=>substr($event->getTicketUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
				'is_physical'=>$event->getIsPhysical()?1:0,
				'is_virtual'=>$event->getIsVirtual()?1:0,
				'is_deleted'=>$event->getIsDeleted()?1:0,
			));

			$stat = $this->db->prepare("INSERT INTO event_history (event_id, summary, description,start_at, end_at, user_account_id  , ".
				"created_at, reverted_from_created_at,venue_id,country_id,timezone,".
				"url, ticket_url, is_physical, is_virtual, area_id, approved_at,is_deleted ) VALUES ".
				"(:event_id, :summary, :description, :start_at, :end_at, :user_account_id  , ".
				":created_at, :reverted_from_created_at,:venue_id,:country_id,:timezone,"."
						:url, :ticket_url, :is_physical, :is_virtual, :area_id, :approved_at, :is_deleted )");
			$stat->execute(array(
				'event_id'=>$event->getId(),
				'summary'=>substr($event->getSummary(),0,VARCHAR_COLUMN_LENGTH_USED),
				'description'=>$event->getDescription(),
				'start_at'=>$event->getStartAtInUTC()->format("Y-m-d H:i:s"),
				'end_at'=>$event->getEndAtInUTC()->format("Y-m-d H:i:s"),
				'venue_id'=>$event->getVenueId(),
				'area_id'=>($event->getVenueId() ? null : $event->getAreaId()),
				'country_id'=>$event->getCountryId(),
				'timezone'=>$event->getTimezone(),
				'user_account_id'=>($user ? $user->getId(): null),
				'created_at'=>$this->timesource->getFormattedForDataBase(),
				'approved_at'=>$this->timesource->getFormattedForDataBase(),
				'reverted_from_created_at'=> ($fromHistory ? date("Y-m-d H:i:s",$fromHistory->getCreatedAtTimeStamp()):null),
				'url'=>substr($event->getUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
				'ticket_url'=>substr($event->getTicketUrl(),0,VARCHAR_COLUMN_LENGTH_USED),
				'is_physical'=>$event->getIsPhysical()?1:0,
				'is_virtual'=>$event->getIsVirtual()?1:0,
				'is_deleted'=>$event->getIsDeleted()?1:0,
			));



			if (!$alreadyInTransaction) {
				$this->db->commit();
			}
		} catch (Exception $e) {
			if (!$alreadyInTransaction) {
				$this->db->rollBack();
			}
			throw $e;
		}

	}


} 
