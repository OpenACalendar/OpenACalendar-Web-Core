<?php



namespace index\forms;

use Silex\Application;
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
class SignUpUserForm  extends AbstractType {


    /** @var Application */
    protected $app;

    function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('email', EmailType::class, array(
			'label'=>'Email',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));
		$builder->add('username', TextType::class, array(
			'label'=>'Username',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
			'attr' => array('autofocus' => 'autofocus')
		));
		$builder->add('password1', PasswordType::class, array(
			'label'=>'Password',
			'required'=>true
		));
		$builder->add('password2', PasswordType::class, array(
			'label'=>'Repeat password',
			'required'=>true
		));

        $builder->add("agree",
            CheckboxType::class,
            array(
                'required' => true,
                'label' => 'I agree to the terms and conditions'
            )
        );
		
		if ($this->app['config']->newUserRegisterAntiSpam) {
			$builder->add('antispam',TextType::class,array('label'=>'What is 2 + 2?','required'=>true));
			
			$myExtraFieldValidatorSpam = function(FormEvent $event){
				$form = $event->getForm();
				$myExtraField = $form->get('antispam')->getData();
				if ($myExtraField != '4' &&  $myExtraField != 'four') {
					$form['antispam']->addError(new FormError("Please prove you are human"));
				}
			};
			$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidatorSpam);
			
		}
		
		/** agree to terms **/
		$myExtraFieldValidator1 = function(FormEvent $event){
			$form = $event->getForm();
			$myExtraField = $form->get('agree')->getData();
			if (empty($myExtraField)) {
				$form['agree']->addError(new FormError("Please agree to the terms and conditions"));
			}
		};
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator1);	
		
		
		/** email looks real **/
		$myExtraFieldValidator2 = function(FormEvent $event){
			$form = $event->getForm();
			$myExtraField = $form->get('email')->getData();
			if (!filter_var($myExtraField, FILTER_VALIDATE_EMAIL)) {
				$form['email']->addError(new FormError("Please enter a email address"));
			}
		};
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator2);
		
		
		/** username alphanumeric **/
		$myExtraFieldValidator3 = function(FormEvent $event){
			$form = $event->getForm();
			$myExtraField = $form->get('username')->getData();
			if (!ctype_alnum($myExtraField) || strlen($myExtraField) < 2) {
				$form['username']->addError(new FormError("Please choose a username with numbers and letters only and at least 2 characters."));
			}
		};
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator3);
		
		/** passwords **/
		$myExtraFieldValidator4 = function(FormEvent $event){
			$form = $event->getForm();
			$myExtraField1 = $form->get('password1')->getData();
			$myExtraField2 = $form->get('password2')->getData();
			if (strlen($myExtraField1) < 2) {
				$form['password1']->addError(new FormError("Please choose a password with at least 2 characters."));
			}
			if ($myExtraField1 != $myExtraField2) {
				$form['password2']->addError(new FormError("Please enter your password again; they did not match."));
			}
		};
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator4);		
	}
	
	public function getName() {
		return 'SignUpUserForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

