<?php

namespace appconfiguration;

use models\VenueModel;
use models\UserAccountModel;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AppConfigurationDefinition {

	protected $extension_id;
	
	protected $key;
	
	protected $type_text = false;
	protected $type_password = false;
	
	protected $editable_in_web_ui = true;

	function __construct(string $extension_id, string $key, string $type, bool $editable_in_web_ui = true) {
		$this->extension_id = $extension_id;
		$this->key = $key;
		if ($type == 'text') {
			$this->type_text = true;
		} elseif ($type == 'password') {
			$this->type_password = true;
		}
		$this->editable_in_web_ui = $editable_in_web_ui;
	}

	public function getExtensionId() {
		return $this->extension_id;
	}

	public function getKey() {
		return $this->key;
	}

	public function isTypeText() {
		return (boolean)$this->type_text;
	}

	public function isTypePassword() {
		return (boolean)$this->type_password;
	}

	public function getEditableInWebUI() {
		return $this->editable_in_web_ui;
	}



	
	
}

