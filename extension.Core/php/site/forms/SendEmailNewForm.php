<?php

namespace site\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class SendEmailNewForm extends AbstractType{

	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('subject', 'text', array(
			'label'=>'Subject',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));
		$builder->add('introduction', 'textarea', array(
			'label'=>'Introduction',
			'required'=>true
		));
		$builder->add('days_into_future', 'number', array(
			'label'=>'Days into future',
			'required'=>true
		));
		
	}
	
	public function getName() {
		return 'SendEmailNewForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}