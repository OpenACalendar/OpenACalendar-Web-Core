<?php



namespace indexapi2\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\UserAccountModel;
use models\API2ApplicationRequestTokenModel;
use models\API2ApplicationModel;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class LogInUserForm  extends AbstractType {
	
	/** @var UserAccountModel 
	 */
	protected $user;


	protected $is_write_calendar;
	protected $is_write_user_actions;

	function __construct(UserAccountModel $user, API2ApplicationModel $app, API2ApplicationRequestTokenModel $requestToken) {
		if (!$app->getIsAutoApprove()) {
			// if the app auto approves permissions we don't ask or tell the user anything about them.
			$this->is_write_calendar = $requestToken ? $requestToken->getIsWriteCalendar() : null;
			$this->is_write_user_actions = $requestToken ? $requestToken->getIsWriteUserActions() : null;
		}
		$this->user = $user;
	}
	
	public function getIsWriteCalendar() {
		return $this->is_write_calendar;
	}

	public function getIsWriteUserActions() {
		return $this->is_write_user_actions;
	}


		
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('username', 'text', array(
			'label'=>'Username',
			'required'=>false, 
			'attr' => array('autofocus' => 'autofocus'),
			'data' => $this->user ? $this->user->getUsername() : '',
		));
		$builder->add('email', 'email', array(
			'label'=>'Email',
			'required'=>false, 
		));
		
		$builder->add('password', 'password', array(
			'label'=>'Password',
			'required'=>true
		));
		
		if ($this->is_write_user_actions) {
			$builder->add("is_write_user_actions",
					"checkbox",
						array(
							'required'=>false,
							'label'=>'Allow this app to alter your personal calendar and lists.',
							'data'=>true,
						)
					);
		}
		if ($this->is_write_calendar) {
			$builder->add("is_write_calendar",
					"checkbox",
						array(
							'required'=>false,
							'label'=>'Allow this app to alter the calendar on your behalf.',
							'data'=>true,
						)
					);
		}
		
	}
	
	public function getName() {
		return 'LogInUserForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

