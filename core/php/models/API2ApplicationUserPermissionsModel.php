<?php

namespace models;

use models\API2ApplicationModel;

/**
 *
 * 
 *  This model does not relate to a DB table directly, but is used in login/auth process.
 * 
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk> 
 */
class API2ApplicationUserPermissionsModel {
	
	protected $is_editor = 0;

	public function setFromData($data) {
		$this->is_editor  = isset($data['is_editor']) ? ($data['is_editor'] ? 1 : -1) : 0;
	}
	
	public function setFromApp(API2ApplicationModel $app) {
		$this->is_editor  = $app->getIsEditor() ? 1 : 0;
	}

	public function getIsEditorGranted() {
		return ($this->is_editor == 1);
	}
	
	public function getIsEditorRefused() {
		return ($this->is_editor == -1);
	}

	public function setIsEditorGranted() {
		$this->is_editor = 1;
	}

	public function setIsEditorRefused() {
		$this->is_editor = -1;
	}


	
}

