<?php



namespace index\forms;

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
class ForgotUserForm  extends AbstractType {
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('username', 'text', array(
			'label'=>'Username',
			'required'=>false, 
			'attr' => array('autofocus' => 'autofocus')
		));
		$builder->add('email', 'email', array(
			'label'=>'Email',
			'required'=>false, 
		));
		
	}
	
	public function getName() {
		return 'ForgotUserForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

