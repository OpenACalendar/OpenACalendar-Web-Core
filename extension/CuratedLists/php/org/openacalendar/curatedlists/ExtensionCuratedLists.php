<?php

namespace org\openacalendar\curatedlists;

use models\UserAccountModel;
use org\openacalendar\curatedlists\userpermissions\CuratedListsChangeUserPermission;

/**
 *
 * @package org.openacalendar.curatedlists
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionCuratedLists extends \BaseExtension {

	public function getId() {
		return 'org.openacalendar.curatedlists';
	}

	public function getTitle() {
		return "Curated Lists";
	}

	public function getDescription() {
		return "Curated Lists";
	}

	public function getTasks() {
		return array(
			new \org\openacalendar\curatedlists\tasks\UpdateCuratedListHistoryChangeFlagsTask($this->app),
			new \org\openacalendar\curatedlists\tasks\UpdateCuratedListFutureEventsCacheTask($this->app),
		);
	}

	public function getAddContentToEventShowPages($parameters) {
		return array(
			new AddContentToEventShowPage($parameters, $this->app),
		);
	}

	public function getSiteFeatures(\models\SiteModel $siteModel = null) {
		return array(
			new \org\openacalendar\curatedlists\sitefeatures\CuratedListFeature(),
		);
	}


    public function getUserPermissions() {
        return array('CURATED_LISTS_CHANGE');
    }

    public function getUserPermission($key) {
        if ($key == 'CURATED_LISTS_CHANGE') {
            return new CuratedListsChangeUserPermission();
        }
    }

    public function canPurgeUser(UserAccountModel $userAccountModel) {

        // Have they ever used this extension?
        $stat = $this->app['db']->prepare("SELECT COUNT(*) AS c FROM curated_list_history ".
            "WHERE curated_list_history.user_account_id =:id");
        $stat->execute(array( 'id'=>$userAccountModel->getId() ));
        if ($stat->fetch()['c'] > 0) {
            return false;
        }

        $stat = $this->app['db']->prepare("SELECT COUNT(*) AS c FROM user_in_curated_list_information ".
            "WHERE user_in_curated_list_information.user_account_id =:id");
        $stat->execute(array( 'id'=>$userAccountModel->getId() ));
        if ($stat->fetch()['c'] > 0) {
            return false;
        }


        // Ok, we are happy.
        return true;
    }

}
