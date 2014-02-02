<?php

namespace site\forms;

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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class GroupEditForm extends AbstractType{

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('title', 'text', array('label'=>'Title','required'=>true, 'attr' => array('autofocus' => 'autofocus')));

		$builder->add('description', 'textarea', array('label'=>'Description','required'=>false));

		$builder->add('url', 'url', array('label'=>'URL','required'=>false));
		$builder->add('twitterUsername', 'text', array('label'=>'Twitter','required'=>false));

		
	}
	
	public function getName() {
		return 'GroupEditForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}