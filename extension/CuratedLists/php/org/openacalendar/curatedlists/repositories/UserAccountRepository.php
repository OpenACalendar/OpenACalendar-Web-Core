<?php


namespace org\openacalendar\curatedlists\repositories;

use org\openacalendar\curatedlists\models\CuratedListModel;
use models\UserAccountModel;


/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserAccountRepository extends \repositories\UserAccountRepository {



	public function loadByOwnerOfCuratedList(CuratedListModel $curatedList) {
		global $DB;
		$stat = $DB->prepare("SELECT user_account_information.* FROM user_account_information ".
				" JOIN user_in_curated_list_information ON user_in_curated_list_information.user_account_id = user_account_information.id ".
				"WHERE user_in_curated_list_information.curated_list_id = :id AND user_in_curated_list_information.is_owner = 't'");
		$stat->execute(array( 'id'=>$curatedList->getId() ));
		if ($stat->rowCount() > 0) {
			$user = new UserAccountModel();
			$user->setFromDataBaseRow($stat->fetch());
			return $user;
		}
	}

}

