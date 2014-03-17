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
class UserEmailsForm  extends AbstractType {
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add("is_email_watch_prompt",
				"checkbox",
					array(
						'required'=>false,
						'label'=>'Send emails when something I watch changes'
					)
			    );
		
		
		$builder->add("is_email_watch_notify",
				"checkbox",
					array(
						'required'=>false,
						'label'=>'Send emails when something I watch is running out of future events'
					)
			    );
		
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
				'n'=>'Don\'t send',
			);
		$builder->add('email_upcoming_events', 'choice', array('label'=>'Email you upcoming events','required'=>true,'choices'=>$choices,'expanded'=>true));
	
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
		
		
	}
	
	public function getName() {
		return 'UserEmailsForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

