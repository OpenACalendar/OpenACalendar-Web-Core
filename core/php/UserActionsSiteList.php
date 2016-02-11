<?php
use Silex\Application;


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

	function __construct(Application $app, \models\SiteModel $siteModel, UserPermissionsList $permissionsList)
	{
        $siteFeatureRepo = new \repositories\SiteFeatureRepository($app);
		$this->actions = array('org.openacalendar'=>array(
			'eventNew'=>$permissionsList->hasPermission("org.openacalendar","EVENTS_CHANGE"),
			'groupNew'=>$permissionsList->hasPermission("org.openacalendar","GROUPS_CHANGE") && $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($siteModel, 'org.openacalendar', 'Group'),
			'tagNew'=>$permissionsList->hasPermission("org.openacalendar","TAGS_CHANGE") && $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($siteModel, 'org.openacalendar', 'Tag'),
			'venueNew'=>$permissionsList->hasPermission("org.openacalendar","VENUES_CHANGE") && $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($siteModel, 'org.openacalendar', 'PhysicalEvents'),
			'areaNew'=>$permissionsList->hasPermission("org.openacalendar","AREAS_CHANGE") && $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($siteModel, 'org.openacalendar', 'PhysicalEvents'),
			'curatedListNew'=>$permissionsList->hasPermission("org.openacalendar.curatedlists","CURATED_LISTS_CHANGE") && $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($siteModel, 'org.openacalendar.curatedlists', 'CuratedList'),
			'curatedListGeneralEdit'=>$permissionsList->hasPermission("org.openacalendar.curatedlists","CURATED_LISTS_CHANGE") && $siteFeatureRepo->doesSiteHaveFeatureByExtensionAndId($siteModel, 'org.openacalendar.curatedlists', 'CuratedList'),
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
