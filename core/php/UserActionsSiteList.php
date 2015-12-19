<?php


/**
 *
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class UserActionsSiteList {


	protected $actions;

	function __construct(\models\SiteModel $siteModel, UserPermissionsList $permissionsList)
	{
		$this->actions = array('org.openacalendar'=>array(
			'eventNew'=>$permissionsList->hasPermission("org.openacalendar","EVENTS_CHANGE"),
			'groupNew'=>$permissionsList->hasPermission("org.openacalendar","GROUPS_CHANGE") && $siteModel->getIsFeatureGroup(),
			'tagNew'=>$permissionsList->hasPermission("org.openacalendar","TAGS_CHANGE") && $siteModel->getIsFeatureTag(),
			'venueNew'=>$permissionsList->hasPermission("org.openacalendar","VENUES_CHANGE") && $siteModel->getIsFeaturePhysicalEvents(),
			'areaNew'=>$permissionsList->hasPermission("org.openacalendar","AREAS_CHANGE") && $siteModel->getIsFeaturePhysicalEvents(),
			'curatedListNew'=>$permissionsList->hasPermission("org.openacalendar","CALENDAR_CHANGE") && $siteModel->getIsFeatureCuratedList(),
			'curatedListGeneralEdit'=>$permissionsList->hasPermission("org.openacalendar","CALENDAR_CHANGE") && $siteModel->getIsFeatureCuratedList(),
			'admin'=>$permissionsList->hasPermission("org.openacalendar","CALENDAR_ADMINISTRATE"),
		));
	}


	public function set($extId, $action, $value) {
		if (!array_key_exists($extId, $this->actions)) {
			$this->actions[$extId] = array();
		}
		$this->actions[$extId][$action] = $value;
	}

	public function has($extId, $action) {
		if (array_key_exists($extId, $this->actions) && array_key_exists($action, $this->actions[$extId])) {
			return $this->actions[$extId][$action];
		}
		return false;
	}

}
