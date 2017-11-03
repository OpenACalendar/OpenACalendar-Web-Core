<?php

namespace sysadmin\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\TextType;


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
		
		$builder->add('action', TextType::class, array('label'=>'Action','required'=>false, 'attr' => array('autocomplete' => 'off')));
		
	}
	
	public function getName() {
		return 'AdminEditorsAddForm';
	}
	
}