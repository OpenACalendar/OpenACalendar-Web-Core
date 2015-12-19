<?php

namespace sysadmin\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ActionForm extends AbstractType{

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('action', 'text', array('label'=>'Action','required'=>false, 'attr' => array('autocomplete' => 'off')));
		
	}
	
	public function getName() {
		return 'AdminEditorsAddForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}