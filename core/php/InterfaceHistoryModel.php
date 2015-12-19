<?php



use Symfony\Component\HttpFoundation\Response;


/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
interface InterfaceHistoryModel {

	public function getSiteEmailTemplate();

	public function getSiteWebTemplate();

	/** @return \DateTime */
	public function getCreatedAt();


	/** @return boolean */
	public function isEqualTo(InterfaceHistoryModel $otherHistoryModel);
}
