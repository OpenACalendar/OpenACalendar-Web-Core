<?php

namespace site\forms;

use models\NewEventDraftModel;
use repositories\SiteFeatureRepository;
use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use models\SiteModel;
use repositories\builders\CountryRepositoryBuilder;
use repositories\builders\VenueRepositoryBuilder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class EventNewWhatDetailsForm extends AbstractType {

	/** @var SiteModel **/
	protected $site;

    protected $siteFeaturePhysicalEvents = false;
    protected $siteFeatureVirtualEvents = false;

    protected $formWidgetTimeMinutesMultiples;

	/** @var  ExtensionManager */
	protected $extensionManager;

	/** @var  NewEventDraftModel */
	protected $eventDraft;

	function __construct(Application $application, NewEventDraftModel $newEventDraftModel) {
		$this->site = $application['currentSite'];
		$this->formWidgetTimeMinutesMultiples = $application['config']->formWidgetTimeMinutesMultiples;
		$this->extensionManager = $application['extensions'];
		$this->eventDraft = $newEventDraftModel;
        $siteFeatureRepo = new SiteFeatureRepository($application);
        $this->siteFeaturePhysicalEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','PhysicalEvents');
        $this->siteFeatureVirtualEvents = $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($this->site,'org.openacalendar','VirtualEvents');
		// TODO $this->fieldIsVirtualDefault = from config!
		// TODO $this->fieldIsPhysicalDefault  = from config!
	}

	protected $fieldIsVirtualDefault = false;

	protected $fieldIsPhysicalDefault = true;

	protected $customFields;

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('summary', TextType::class, array(
			'label'=>'Summary',
			'required'=>true, // TODO THIS IS NOT RESPCTED
			'max_length'=>VARCHAR_COLUMN_LENGTH_USED, 
			'attr' => array('autofocus' => 'autofocus'),
			'data' => $this->eventDraft->getDetailsValue('event.summary'),
		));
		
		$builder->add('description', TextareaType::class, array(
			'label'=>'Description',
			'required'=>false,
			'data' => $this->eventDraft->getDetailsValue('event.description'),
		));
		
		
		$builder->add('url', new \symfony\form\MagicUrlType(), array(
			'label'=>'Information Web Page URL',
			'required'=>false,
			'data' => $this->eventDraft->getDetailsValue('event.url'),
		));
		
		$builder->add('ticket_url', new \symfony\form\MagicUrlType(), array(
			'label'=>'Tickets Web Page URL',
			'required'=>false,
			'data' => $this->eventDraft->getDetailsValue('event.ticket_url'),
		));


		$this->customFields = array();
		foreach($this->site->getCachedEventCustomFieldDefinitionsAsModels() as $customField) {
			if ($customField->getIsActive()) {
				$extension = $this->extensionManager->getExtensionById($customField->getExtensionId());
				if ($extension) {
					$fieldType = $extension->getEventCustomFieldByType($customField->getType());
					if ($fieldType) {
						$this->customFields[] = $customField;
						$options = $fieldType->getSymfonyFormOptions($customField);
						$options['data'] = $this->eventDraft->getDetailsValue('event.custom.'.$customField->getKey());
						$builder->add('custom_' . $customField->getKey(), $fieldType->getSymfonyFormType($customField), $options);
					}
				}
			}
		}

		if ($this->siteFeatureVirtualEvents) {

			//  if both are an option, user must check which one.
			if ($this->siteFeaturePhysicalEvents) {

				$builder->add("is_virtual",
                    CheckboxType::class,
					array(
						'required'=>false,
						'label'=>'Is event accessible online?',
						'data'=>$this->eventDraft->hasDetailsValue('event.is_virtual') ?  $this->eventDraft->getDetailsValue('event.is_virtual') : $this->fieldIsVirtualDefault,
					)
				);
			} else {
				$builder->add('is_virtual', HiddenType::class, array( 'data' => true, ));
			}

		} else {
			$builder->add('is_virtual', HiddenType::class, array( 'data' => false, ));

		}

		if ($this->siteFeaturePhysicalEvents) {

			//  if both are an option, user must check which one.
			if ($this->siteFeatureVirtualEvents) {

				$builder->add("is_physical",
                    CheckboxType::class,
					array(
						'required'=>false,
						'label'=>'Does the event happen at a place?',
						'data'=>$this->eventDraft->hasDetailsValue('event.is_physical') ?  $this->eventDraft->getDetailsValue('event.is_physical') : $this->fieldIsPhysicalDefault,
					)
				);

			} else {
				$builder->add('is_physical', HiddenType::class, array( 'data' => true, ));

			}

		} else {

			$builder->add('is_physical', HiddenType::class, array( 'data' => false, ));
		}

		/** @var \closure $myExtraFieldValidator **/
		$myExtraFieldValidator = function(FormEvent $event){
			$form = $event->getForm();
			// URL validation. We really can't do much except verify ppl haven't put a space in, which they might do if they just type in Google search terms (seen it done)
			if (strpos($form->get("url")->getData(), " ") !== false) {
				$form['url']->addError(new FormError("Please enter a URL"));
			}
			if (strpos($form->get("ticket_url")->getData(), " ") !== false) {
				$form['ticket_url']->addError(new FormError("Please enter a URL"));
			}
			// Title
			if (!trim($form->get('summary')->getData())) {
				$form->get('summary')->addError( new FormError("Please enter a summary"));
			}
			// TODO it has to be at least one or the other of physical or virtual
		};



		// adding the validator to the FormBuilderInterface
		$builder->addEventListener(FormEvents::POST_BIND, $myExtraFieldValidator);	
	}
	
	public function getName() {
		return 'EventNewWhatDetailsForm';
	}
	
	public function getDefaultOptions(array $options) {
		return array(
		);
	}

	/**
	 * @return mixed
	 */
	public function getCustomFields()
	{
		return $this->customFields;
	}

}


