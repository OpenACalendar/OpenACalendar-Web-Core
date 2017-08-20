<?php



namespace index\forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;

use \ExtensionManager;
use repositories\UserNotificationPreferenceRepository;
use models\UserAccountModel;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserEmailsForm  extends AbstractType {
	
	protected $preferences;
	protected $user;

	public function __construct(\ExtensionManager $extensionManager, UserAccountModel $user) {
		foreach($extensionManager->getExtensionsIncludingCore() as $extension) {
			$extID = $extension->getId();
			foreach($extension->getUserNotificationPreferenceTypes() as $type) {
				$key = str_replace(".", "_", $extID.'.'.$type);
				$this->preferences[$key] = $extension->getUserNotificationPreference($type);
			}
		}
		$this->user = $user;
	}
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		global $app;

		$choices = array(
				'None'=>'n',
				'You are attending'=>'a',
				'You are or might be attending'=>'m',
				'You are or might be attending, or you watch the event'=>'w',
			);
		$builder->add('email_upcoming_events', ChoiceType::class, array('label'=>'Notify you of upcoming events','required'=>true,'choices'=>$choices,'expanded'=>true, 'choices_as_values'=>true));
	
		$builder->add("email_upcoming_events_days_notice",
				NumberType::class,
					array(
						'required'=>true,
						'precision'=>0,
						'label'=>'For upcoming events, how many days notice do you want?'
					)
			    );
		
		$repo = new \repositories\UserNotificationPreferenceRepository($app);
		
		foreach($this->preferences as $key=>$preference) {
			
			$userPref = $repo->load($this->user, $preference->getUserNotificationPreferenceExtensionID(), 
					$preference->getUserNotificationPreferenceType());

            $builder->add($key,
                CheckboxType::class,
                array(
                    'required' => false,
                    'label' => $preference->getLabel(),
                    'mapped' => false,
                    'data' => $userPref->getIsEmail(),
                )
            );

		}

		
	}
	
	public function getName() {
		return 'UserEmailsForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
	public function savePreferences($form) {
        global $app;
		$repo = new \repositories\UserNotificationPreferenceRepository($app);
		foreach($this->preferences as $key=>$preference) {
			$repo->editEmailPreference($this->user, $preference->getUserNotificationPreferenceExtensionID(), 
					$preference->getUserNotificationPreferenceType(), $form->get($key)->getData());
		}
	}
	
	
}

