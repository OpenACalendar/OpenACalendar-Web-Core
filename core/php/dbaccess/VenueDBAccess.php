<?php


namespace dbaccess;

use models\UserAccountModel;
use models\VenueModel;
use sysadmin\controllers\API2Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class VenueDBAccess {

	/** @var  \PDO */
	protected $db;

	/** @var  \TimeSource */
	protected $timesource;


	function __construct($db, $timesource)
	{
		$this->db = $db;
		$this->timesource = $timesource;
	}


	protected $possibleFields = array('title','lat','lng','description','address','address_code','country_id','area_id','is_duplicate_of_id','is_deleted');

	public function update(VenueModel $venue, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		// Make Information Data
		$fieldsSQL1 = array();
		$fieldsParams1 = array( 'id'=>$venue->getId() );
		foreach($fields as $field) {
			$fieldsSQL1[] = " ".$field."=:".$field." ";
			if ($field == 'title') {
				$fieldsParams1['title'] = substr($venue->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'lat') {
				$fieldsParams1['lat'] = $venue->getLat();
			} else if ($field == 'lng') {
				$fieldsParams1['lng'] = $venue->getLng();
			} else if ($field == 'description') {
				$fieldsParams1['description'] = $venue->getDescription();
			} else if ($field == 'address') {
				$fieldsParams1['address'] = $venue->getAddress();
			} else if ($field == 'address_code') {
				$fieldsParams1['address_code'] = substr($venue->getAddressCode(),0,VARCHAR_COLUMN_LENGTH_USED);
			} else if ($field == 'country_id') {
				$fieldsParams1['country_id'] = $venue->getCountryId();
			} else if ($field == 'area_id') {
				$fieldsParams1['area_id'] = $venue->getAreaId();
			} else if ($field == 'is_duplicate_of_id') {
				$fieldsParams1['is_duplicate_of_id'] = $venue->getIsDuplicateOfId();
			} else if ($field == 'is_deleted') {
				$fieldsParams1['is_deleted'] = ($venue->getIsDeleted()?1:0);
			}
		}

		// Make History Data
		$fieldsSQL2 = array('venue_id','user_account_id','created_at','approved_at');
		$fieldsSQLParams2 = array(':venue_id',':user_account_id',':created_at',':approved_at');
		$fieldsParams2 = array(
			'venue_id'=>$venue->getId(),
			'user_account_id'=>($user ? $user->getId() : null),
			'created_at'=>$this->timesource->getFormattedForDataBase(),
			'approved_at'=>$this->timesource->getFormattedForDataBase(),
		);
		foreach($this->possibleFields as $field) {
			if (in_array($field, $fields)) {
				$fieldsSQL2[] = " ".$field." ";
				$fieldsSQLParams2[] = " :".$field." ";
				if ($field == 'title') {
					$fieldsParams2['title'] = substr($venue->getTitle(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'lat') {
					$fieldsParams2['lat'] = $venue->getLat();
				} else if ($field == 'lng') {
					$fieldsParams2['lng'] = $venue->getLng();
				} else if ($field == 'description') {
					$fieldsParams2['description'] = $venue->getDescription();
				} else if ($field == 'address') {
					$fieldsParams2['address'] = $venue->getAddress();
				} else if ($field == 'address_code') {
					$fieldsParams2['address_code'] = substr($venue->getAddressCode(),0,VARCHAR_COLUMN_LENGTH_USED);
				} else if ($field == 'country_id') {
					$fieldsParams2['country_id'] = $venue->getCountryId();
				} else if ($field == 'area_id') {
					$fieldsParams2['area_id'] = $venue->getAreaId();
				} else if ($field == 'is_duplicate_of_id') {
					$fieldsParams2['is_duplicate_of_id'] = $venue->getIsDuplicateOfId();
				} else if ($field == 'is_deleted') {
					$fieldsParams2['is_deleted'] = ($venue->getIsDeleted()?1:0);
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
			$stat = $this->db->prepare("UPDATE venue_information  SET ".implode(",", $fieldsSQL1)." WHERE id=:id");
			$stat->execute($fieldsParams1);

			// History SQL
			$stat = $this->db->prepare("INSERT INTO venue_history (".implode(",",$fieldsSQL2).") VALUES (".implode(",",$fieldsSQLParams2).")");
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
