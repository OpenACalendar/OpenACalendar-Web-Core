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

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) 2013-2014, JMB Technology Limited, http://jmbtechnology.co.uk/
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
		

		
		$builder->add("is_email_watch_import_expired",
				"checkbox",
					array(
						'required'=>false,
						'label'=>'Send emails when something I watch has an importer that expires'
					)
			    );
		
		$choices = array(
				'a'=>'You are attending',
				'm'=>'You are or might be attending',
				'w'=>'You are or might be attending, or you watch the event',
			);
		$builder->add('email_upcoming_events', 'choice', array('label'=>'Notify you of upcoming events','required'=>true,'choices'=>$choices,'expanded'=>true));
	
		$builder->add("email_upcoming_events_days_notice",
				"number",
					array(
						'required'=>true,
						'precision'=>0,
						'label'=>'For upcoming events, how many days notice do you want?'
					)
			    );
		
		
		$builder->add("is_email_newsletter",
				"checkbox",
					array(
						'required'=>false,
						'label'=>'Send newsletters (never more than one per month)'
					)
			    );
		
		
		$repo = new \repositories\UserNotificationPreferenceRepository();
		
		foreach($this->preferences as $key=>$preference) {
			
			$userPref = $repo->load($this->user, $preference->getUserNotificationPreferenceExtensionID(), 
					$preference->getUserNotificationPreferenceType());
			
			$builder->add($key,
					"checkbox",
						array(
							'required'=>false,
							'label'=>$preference->getLabel(),
							'mapped'=>false,
							'data'=>$userPref->getIsEmail(),
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
		$repo = new \repositories\UserNotificationPreferenceRepository();
		foreach($this->preferences as $key=>$preference) {
			$repo->editEmailPreference($this->user, $preference->getUserNotificationPreferenceExtensionID(), 
					$preference->getUserNotificationPreferenceType(), $form->get($key)->getData());
		}
	}
	
	
}

