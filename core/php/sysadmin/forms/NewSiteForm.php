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
class NewSiteForm  extends AbstractType {
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('email', 'email', array('label'=>'Email Of Owner','required'=>true));

		// The rest of this is duplicated from index\forms\CreateForm

		$builder->add('title', 'text', array(
			'label'=>'Title',
			'required'=>true,
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED,
		));

		$builder->add('slug', 'text', array(
			'label'=>'Slug For Web Address',
			'required'=>true,
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED
		));

		$myExtraFieldValidator = function(FormEvent $event){
			global $CONFIG;
			$form = $event->getForm();
			$myExtraField = $form->get('slug')->getData();
			if (!ctype_alnum($myExtraField) || strlen($myExtraField) < 2) {
				$form['slug']->addError(new FormError("Numbers and letters only, at least 2."));
			} else if (in_array($myExtraField, $CONFIG->siteSlugReserved)) {
				$form['slug']->addError(new FormError("That is already taken."));
			}
		};
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);

		$readChoices = array(
			'public'=>'Public, and listed on search engines and our directory',
			'protected'=>'Public, but not listed so only people who know about it can find it',
		);
		$builder->add('read', 'choice', array('label'=>'Who can read?','required'=>true,'choices'=>$readChoices,'expanded'=>true));
		$builder->get('read')->setData( 'public' );

		$writeChoices = array(
			'public'=>'Anyone can add data',
			'protected'=>'Only people I say can add data',
		);
		$builder->add('write', 'choice', array('label'=>'Who can write?','required'=>true,'choices'=>$writeChoices,'expanded'=>true));
		$builder->get('write')->setData( 'public' );

	}
	
	public function getName() {
		return 'NewSiteForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}
	
}

