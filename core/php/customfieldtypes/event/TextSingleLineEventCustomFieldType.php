<?php

namespace customfieldtypes\event;
use InterfaceEventCustomFieldType;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2015, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class TextSingleLineEventCustomFieldType implements InterfaceEventCustomFieldType {

	public function getSymfonyFormType(\models\EventCustomFieldDefinitionModel $eventCustomFieldDefinitionModel)
	{
		return 'text';
	}

	public function getSymfonyFormOptions(\models\EventCustomFieldDefinitionModel $eventCustomFieldDefinitionModel)
	{
		return array(
			'label'=>$eventCustomFieldDefinitionModel->getLabel(),
			'mapped'=>false,
			'required'=>false,
		);
	}

}