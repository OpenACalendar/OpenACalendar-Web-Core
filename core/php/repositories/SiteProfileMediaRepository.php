<?php


namespace repositories;

use models\SiteModel;
use models\UserAccountModel;

/**
 *
 * Note this only saves! All information is loaded thru normal Site repository and model.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SiteProfileMediaRepository {
	
	
	public function createOrEdit(SiteModel $site, UserAccountModel $user) {
		global $DB;
		$createdat = \TimeSource::getFormattedForDataBase();
		
		try {
			$DB->beginTransaction();

			$stat = $DB->prepare("SELECT * FROM site_profile_media_information WHERE site_id=:site_id");
			$stat->execute(array(
				'site_id'=>$site->getId(),
			));
			if ($stat->rowCount() == 1) {
				$stat = $DB->prepare("UPDATE site_profile_media_information SET logo_media_id=:logo_media_id ".
						" WHERE site_id=:site_id");
			} else {
				$stat = $DB->prepare("INSERT INTO site_profile_media_information (site_id, logo_media_id) ".
					" VALUES (:site_id, :logo_media_id)");
			}
			$stat->execute(array(
					'logo_media_id'=>$site->getLogoMediaId(),
					'site_id'=>$site->getId(),
				));
			
			$stat = $DB->prepare("INSERT INTO site_profile_media_history (site_id, logo_media_id, user_account_id, created_at) ".
					" VALUES (:site_id, :logo_media_id, :user_account_id, :created_at)");
			$stat->execute(array(
					'site_id'=>$site->getId(),
					'logo_media_id'=>$site->getLogoMediaId(),
					'created_at'=>  $createdat,
					'user_account_id'=>$user->getId(), 
				));
			$data = $stat->fetch();
			
			
			$DB->commit();
		} catch (Exception $e) {
			$DB->rollBack();
		}
	}
	
	
}

