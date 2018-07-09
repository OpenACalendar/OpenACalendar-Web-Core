<?php

namespace org\openacalendar\contact;

use models\UserAccountModel;

/**
 *
 * @package org.openacalendar.contact
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ExtensionContact extends \BaseExtension {

	public function getId() {
		return 'org.openacalendar.contact';
	}

	public function getTitle() {
		return "Contact";
	}

	public function getDescription() {
		return "Contact Us Pages";
	}

	public function getSysAdminLinks() {
		return array(array('title'=>'Contact Support','url'=>'/sysadmin/contactsupport'));
	}

    public function canPurgeUser(UserAccountModel $userAccountModel) {

        // Have they ever used contact?
        $stat = $this->app['db']->prepare("SELECT COUNT(*) AS c FROM contact_support ".
            "WHERE contact_support.user_account_id =:id");
        $stat->execute(array( 'id'=>$userAccountModel->getId() ));
        if ($stat->fetch()['c'] > 0) {
            return false;
        }


        // Ok, we are happy.
        return true;
    }
}
