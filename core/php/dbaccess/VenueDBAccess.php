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


	public function update(VenueModel $venue, $fields, UserAccountModel $user = null ) {
		$alreadyInTransaction = $this->db->inTransaction();

		try {
			if (!$alreadyInTransaction) {
				$this->db->beginTransaction();
			}

			$stat = $this->db->prepare("UPDATE venue_information  SET title=:title,description=:description,".
				"lat=:lat,lng=:lng , country_id=:country_id, area_id=:area_id, address=:address, ".
				"address_code=:address_code, is_deleted=:is_deleted WHERE id=:id");
			$stat->execute(array(
				'id'=>$venue->getId(),
				'title'=>$venue->getTitle(),
				'lat'=>$venue->getLat(),
				'lng'=>$venue->getLng(),
				'description'=>$venue->getDescription(),
				'address'=>$venue->getAddress(),
				'address_code'=>$venue->getAddressCode(),
				'country_id'=>$venue->getCountryId(),
				'area_id'=>$venue->getAreaId(),
				'is_deleted'=>($venue->getIsdeleted()?1:0),
			));

			$stat = $this->db->prepare("INSERT INTO venue_history (venue_id, title, lat,lng,country_id, ".
				"area_id, description, user_account_id  , created_at,approved_at,address,address_code,is_duplicate_of_id,is_deleted) VALUES ".
				"(:venue_id, :title, :lat, :lng, :country_id,".
				":area_id,:description,  :user_account_id  , :created_at,:approved_at,:address,:address_code,:is_duplicate_of_id,:is_deleted)");
			$stat->execute(array(
				'venue_id'=>$venue->getId(),
				'title'=>$venue->getTitle(),
				'lat'=>$venue->getLat(),
				'lng'=>$venue->getLng(),
				'description'=>$venue->getDescription(),
				'address'=>$venue->getAddress(),
				'address_code'=>$venue->getAddressCode(),
				'user_account_id'=>($user ? $user->getId() : null),
				'country_id'=>$venue->getCountryId(),
				'is_duplicate_of_id'=>$venue->getIsDuplicateOfId(),
				'area_id'=>$venue->getAreaId(),
				'created_at'=>$this->timesource->getFormattedForDataBase(),
				'approved_at'=>$this->timesource->getFormattedForDataBase(),
				'is_deleted'=>($venue->getIsdeleted()?1:0),
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
