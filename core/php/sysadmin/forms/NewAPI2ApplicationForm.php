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
class NewAPI2ApplicationForm  extends AbstractType {
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('email', 'email', array('label'=>'Email Of Owner','required'=>true));
		$builder->add('title', 'text', array('label'=>'Title','required'=>true));
		
	}
	
	public function getName() {
		return 'NewAPI2ApplicationForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

