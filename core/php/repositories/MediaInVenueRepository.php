<?php



namespace repositories;

use models\MediaModel;
use models\UserAccountModel;
use models\VenueModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */

class MediaInVenueRepository{
	
	
	public function add(MediaModel $media, VenueModel $venue, UserAccountModel $user) {
		global $DB;
		
		// check event not already in list
		$stat = $DB->prepare("SELECT * FROM media_in_venue WHERE venue_id=:venue_id AND ".
				" media_id=:media_id AND removed_at IS NULL ");
		$stat->execute(array(
			'venue_id'=>$venue->getId(),
			'media_id'=>$media->getId(),
		));
		if ($stat->rowCount() > 0) {
			return;
		}
		
		// Add!
		$stat = $DB->prepare("INSERT INTO media_in_venue (venue_id,media_id,added_by_user_account_id,added_at,addition_approved_at) ".
				"VALUES (:venue_id,:media_id,:added_by_user_account_id,:added_at,:addition_approved_at)");
		$stat->execute(array(
			'venue_id'=>$venue->getId(),
			'media_id'=>$media->getId(),
			'added_by_user_account_id'=>$user->getId(),
			'added_at'=>  \TimeSource::getFormattedForDataBase(),
			'addition_approved_at'=>  \TimeSource::getFormattedForDataBase(),
		));
		
	}


	public function remove(MediaModel $media, VenueModel $venue, UserAccountModel $user) {
		global $DB;
		$stat = $DB->prepare("UPDATE media_in_venue SET removed_by_user_account_id=:removed_by_user_account_id,".
				" removed_at=:removed_at, removal_approved_at=:removal_approved_at WHERE ".
				" venue_id=:venue_id AND media_id=:media_id AND removed_at IS NULL ");
		$stat->execute(array(
				'venue_id'=>$venue->getId(),
				'media_id'=>$media->getId(),
				'removed_at'=>  \TimeSource::getFormattedForDataBase(),
				'removal_approved_at'=>  \TimeSource::getFormattedForDataBase(),
				'removed_by_user_account_id'=>$user->getId(),
			));
	}
}

