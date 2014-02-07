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
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class ResetUserForm  extends AbstractType {
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('password1', 'password', array(
			'label'=>'Password',
			'required'=>true, 
			'attr' => array('autofocus' => 'autofocus')
		));
		$builder->add('password2', 'password', array(
			'label'=>'Repeat password',
			'required'=>true
		));	

		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			$myExtraField1 = $form->get('password1')->getData();
			$myExtraField2 = $form->get('password2')->getData();
			if (strlen($myExtraField1) < 2) {
				$form['password1']->addError(new FormError("Password: at least 2 characters."));
			}
			if ($myExtraField1 != $myExtraField2) {
				$form['password2']->addError(new FormError("Passwords do not match"));
			}
		};
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);		
	}
	
	public function getName() {
		return 'ResetUserForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

