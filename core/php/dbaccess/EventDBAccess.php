<?php


namespace dbaccess;

use models\UserAccountModel;
use models\EventModel;
use models\EventHistoryModel;
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


	protected $possibleFields = array('summary','description','start_at','end_at','venue_id','area_id','country_id','timezone',
		'url','ticket_url','is_physical','is_virtual','is_cancelled','is_deleted','is_duplicate_of_id');


	public function update(EventModel $event, $fields, UserAccountModel $user = null, EventHistoryModel $fromHistory = null ) {
		$alreadyInTransaction = $this->db->inTransaction();


		// Make Information Data
		$fieldsSQL1 = array();
		$fieldsParams1 = array( 'id'=>$event->getId() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'summary') {
				$fieldsParams1['summary'] = $event->getSummary();
			} else if ($field == 'description') {
				$fieldsParams1['description'] = $event->getDescription();
			} else if ($field == 'start_at') {
				$fieldsParams1['start_at'] = $event->getStartAt()->format("Y-m-d H:i:s");
			} else if ($field == 'end_at') {
				$fieldsParams1['end_at'] = $event->getEndAt()->format("Y-m-d H:i:s");
			} else if ($field == 'venue_id') {
				$fieldsParams1['venue_id'] = $event->getVenueId();
			} else if ($field == 'area_id') {
				$fieldsParams1['area_id'] = $event->getAreaId();
			} else if ($field == 'country_id') {
				$fieldsParams1['country_id'] = $event->getCountryId();
			} else if ($field == 'timezone') {
				$fieldsParams1['timezone'] = $event->getTimezone();
			} else if ($field == 'url') {
				$fieldsParams1['url'] = $event->getUrl();
			} else if ($field == 'ticket_url') {
				$fieldsParams1['ticket_url'] = $event->getTicketUrl();
			} else if ($field == 'is_physical') {
				$fieldsParams1['is_physical'] = $event->getIsPhysical() ? 1 : 0;
			} else if ($field == 'is_virtual') {
				$fieldsParams1['is_virtual'] = $event->getIsVirtual() ? 1 : 0;
			} else if ($field == 'is_cancelled') {
				$fieldsParams1['is_cancelled'] = $event->getIsCancelled() ? 1 : 0;
			} else if ($field == 'is_deleted') {
				$fieldsParams1['is_deleted'] = $event->getIsDeleted() ? 1 : 0;
			} else if ($field == 'is_duplicate_of_id') {
				$fieldsParams1['is_duplicate_of_id'] = $event->getIsDuplicateOfId();
			}
		}

		// Make History Data
		$fieldsSQL2 = array('event_id','user_account_id','created_at','approved_at');
		$fieldsSQLParams2 = array(':event_id',':user_account_id',':created_at',':approved_at');
		$fieldsParams2 = array(
			'event_id'=>$event->getId(),
			'user_account_id'=>($user ? $user->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
			'approved_at'=>$this->timesource->getFormattedForDataBase(),
		);
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields)) {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'summary') {
					$fieldsParams2['summary'] = $event->getSummary();
				} else if ($field == 'description') {
					$fieldsParams2['description'] = $event->getDescription();
				} else if ($field == 'start_at') {
					$fieldsParams2['start_at'] = $event->getStartAt()->format("Y-m-d H:i:s");
				} else if ($field == 'end_at') {
					$fieldsParams2['end_at'] = $event->getEndAt()->format("Y-m-d H:i:s");
				} else if ($field == 'venue_id') {
					$fieldsParams2['venue_id'] = $event->getVenueId();
				} else if ($field == 'area_id') {
					$fieldsParams2['area_id'] = $event->getAreaId();
				} else if ($field == 'country_id') {
					$fieldsParams2['country_id'] = $event->getCountryId();
				} else if ($field == 'timezone') {
					$fieldsParams2['timezone'] = $event->getTimezone();
				} else if ($field == 'url') {
					$fieldsParams2['url'] = $event->getUrl();
				} else if ($field == 'ticket_url') {
					$fieldsParams2['ticket_url'] = $event->getTicketUrl();
				} else if ($field == 'is_physical') {
					$fieldsParams2['is_physical'] = $event->getIsPhysical() ? 1 : 0;
				} else if ($field == 'is_virtual') {
					$fieldsParams2['is_virtual'] = $event->getIsVirtual() ? 1 : 0;
				} else if ($field == 'is_cancelled') {
					$fieldsParams2['is_cancelled'] = $event->getIsCancelled() ? 1 : 0;
				} else if ($field == 'is_deleted') {
					$fieldsParams2['is_deleted'] = $event->getIsDeleted() ? 1 : 0;
				} else if ($field == 'is_duplicate_of_id') {
					$fieldsParams2['is_duplicate_of_id'] = $event->getIsDuplicateOfId();
				}
				$fieldsSQL2[] = " ".$field."_changed ";
				$fieldsSQLParams2[] = " 0 ";
			} else {
				$fieldsSQL2[] = " ".$field."_changed ";
				$fieldsSQLParams2[] = " -2 ";
			}
		}



		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			// Information SQL
			$stat = $this->db->prepare("UPDATE event_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO event_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
			$stat->execute($fieldsParams2);

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
