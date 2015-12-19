<?php

namespace appconfiguration;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class AppConfigurationManager {
	
	/** @var PDO **/
	protected $DB;
	protected $CONFIG;
			
	function __construct($DB, $CONFIG) {
		$this->DB = $DB;
		$this->CONFIG = $CONFIG;
	}

	public function getValue(AppConfigurationDefinition $config, $default=null) {
		
		$stat = $this->DB->prepare("SELECT value_text FROM app_configuration_information ".
				"WHERE extension_id=:e AND configuration_key=:k");
		$stat->execute(array('e'=>$config->getExtensionId(),'k'=>$config->getKey()));
		$dbdata = $stat->fetch();
		
		if ($dbdata && $dbdata['value_text'] && ($config->isTypeText() || $config->isTypePassword())) {
			return $dbdata['value_text'];
		}
		
		return $default;
		
	}

	public function setValue(AppConfigurationDefinition $config, $value) {
		
		$stat = $this->DB->prepare("SELECT value_text FROM app_configuration_information ".
			"WHERE extension_id=:e AND configuration_key=:k");
		$stat->execute(array(
				'e'=>$config->getExtensionId(),
				'k'=>$config->getKey()
			));
		if ($stat->rowCount() == 1) {
			
			$stat = $this->DB->prepare("UPDATE app_configuration_information SET value_text=:text ".
				"WHERE extension_id=:e AND configuration_key=:k");
			$stat->execute(array(
					'e'=>$config->getExtensionId(),
					'k'=>$config->getKey(),
					'text'=>$value,
				));
			
		} else {
			
			$stat = $this->DB->prepare("INSERT INTO app_configuration_information  ".
					"(extension_id,configuration_key,value_text) ".
					"VALUES (:e,:k,:text)");
			$stat->execute(array(
					'e'=>$config->getExtensionId(),
					'k'=>$config->getKey(),
					'text'=>$value,
				));
			
		}
				
	}
	
}

