<?php



namespace index\forms;

use models\UserAccountModel;
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
class ContactForm  extends AbstractType {

	/** @var  UserAccountModel */
	protected $currentUser;

	function __construct(UserAccountModel $currentUser = null)
	{
		$this->currentUser = $currentUser;
	}


	public function buildForm(FormBuilderInterface $builder, array $options) {
		global $CONFIG;
		
		$builder->add('subject', 'text', array(
			'label'=>'Subject',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
			'attr' => array('autofocus' => 'autofocus')
		));
		

		$builder->add('email', 'email', array(
			'label'=>'Email',
			'required'=>true, 
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED,
			'data' => $this->currentUser ? $this->currentUser->getEmail() : '',
		));
		
		$builder->add('message', 'textarea', array(
			'label'=>'Message',
			'required'=>true, 
		));

		
		if ($CONFIG->contactFormAntiSpam && !$this->currentUser) {
			$builder->add('antispam','text',array('label'=>'What is 2 + 2?','required'=>true));
			
			$myExtraFieldValidatorSpam = function(FormEvent $event){
				$form = $event->getForm();
				$myExtraField = $form->get('antispam')->getData();
				if ($myExtraField != '4' &&  $myExtraField != 'four') {
					$form['antispam']->addError(new FormError("Please prove you are human"));
				}
			};
			$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidatorSpam);
			
		}
		
	}
	
	public function getName() {
		return 'SignUpUserForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

