<?php



namespace index\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LogInUserForm  extends AbstractType {
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('email', EmailType::class, array(
			'label'=>'Email',
			'required'=>true,
			'attr' => array('autofocus' => 'autofocus')
		));
		
		$builder->add('password', PasswordType::class, array(
			'label'=>'Password',
			'required'=>true
		));

        $builder->add("rememberme",
            CheckboxType::class,
            array(
                'required' => false,
                'label' => 'Remember Me'
            )
        );
        $builder->get('rememberme')->setData(true);
		
	}
	
	public function getName() {
		return 'LogInUserForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

