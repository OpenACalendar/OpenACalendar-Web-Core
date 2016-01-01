<?php

namespace tasks;


use models\ImportModel;
use repositories\builders\ImportRepositoryBuilder;
use repositories\SiteFeatureRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportRepository;
use import\ImportRunner;
use repositories\builders\UserWatchesGroupRepositoryBuilder;
use repositories\builders\UserWatchesSiteRepositoryBuilder;
use repositories\UserAccountRepository;
use repositories\UserWatchesSiteStopRepository;
use repositories\UserWatchesGroupStopRepository;
use repositories\UserAccountGeneralSecurityKeyRepository;
use repositories\UserNotificationRepository;
use Silex\Application;

/**
 *
 * @package Core
 * @link http://ican.openacalendar.org/ OpenACalendar Open Source Software
 * @license http://ican.openacalendar.org/license.html 3-clause BSD
 * @copyright (c) JMB Technology Limited, http://jmbtechnology.co.uk/
 * @author James Baster <james@jarofgreen.co.uk>
 */
class RunImportsTask extends \BaseTask  {



	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'RunImports';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskRunImportURLsAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskRunImportURLsAutomaticUpdateInterval;
	}

	protected function run() {

		$iurlBuilder = new ImportRepositoryBuilder();

		foreach($iurlBuilder->fetchAll() as $import) {
            $this->runImport($import);
        }
        return array('result'=>'ok');

    }

    public function runImport(ImportModel $import) {
        global $CONFIG;

        $siteRepo = new SiteRepository();
        $siteFeatureRepository = new SiteFeatureRepository($this->app);
        $groupRepo = new GroupRepository();
        $importURLRepo = new ImportRepository();
        $userRepo = new UserAccountRepository();
        $userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
        $userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
        $userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
        $userNotificationRepo = new UserNotificationRepository();

        /** @var usernotifications/UpcomingEventsUserNotificationType **/
        $userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('ImportURLExpired');

        $site = $siteRepo->loadById($import->getSiteID());
        $importerFeature = $siteFeatureRepository->doesSiteHaveFeatureByExtensionAndId($site, 'org.openacalendar','Importer');
        $group = $groupRepo->loadById($import->getGroupId());

        $this->logVerbose(" ImportURL ".$import->getId()." ".$import->getTitle()." Site ".$site->getTitle());

        if ($site->getIsClosedBySysAdmin()) {
            $this->logVerbose( " - site closed by sys admin");
        } else if (!$importerFeature) {
            $this->logVerbose( " - site feature disabled");
        } else if (!$group) {
            $this->logVerbose( " - no group - this should be impossible");
        } else if ($group->getIsDeleted()) {
            $this->logVerbose( " - group deleted");
        } else if ($import->getExpiredAt()) {
            $this->logVerbose( " - expired");
        } else if (!$import->getIsEnabled()) {
            $this->logVerbose( " - not enabled");
        } else if ($import->isShouldExpireNow()) {
            $this->logVerbose( " - expiring" );
            $importURLRepo->expire($import);

            configureAppForSite($site);

            $uwsb = new UserWatchesSiteRepositoryBuilder();
            $uwsb->setSite($site);
            foreach ($uwsb->fetchAll() as $userWatchesSite) {
                $user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
                if ($userWatchesSite->getIsWatching()) {

                    /// Notification Class
                    $userNotification = $userNotificationType->getNewNotification($user, $site);
                    $userNotification->setImport($import);
                    $userNotification->setGroup($group);

                    ////// Save Notification Class
                    $userNotificationRepo->create($userNotification);

                    ////// Send Email
                    if ($userNotification->getIsEmail()) {

                        configureAppForUser($user);
                        $userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
                        $userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);

                        $message = \Swift_Message::newInstance();
                        $message->setSubject("Please confirm this is still valid: ".$import->getTitle());
                        $message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
                        $message->setTo($user->getEmail());

                        $messageText = $this->app['twig']->render('email/importExpired.watchesSite.txt.twig', array(
                            'user'=>$user,
                            'import'=>$import,
                            'stopCode'=>$userWatchesSiteStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importExpired.watchesSite.txt', $messageText);
                        $message->setBody($messageText);

                        $messageHTML = $this->app['twig']->render('email/importExpired.watchesSite.html.twig', array(
                            'user'=>$user,
                            'import'=>$import,
                            'stopCode'=>$userWatchesSiteStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importExpired.watchesSite.html', $messageHTML);
                        $message->addPart($messageHTML,'text/html');

                        if (!$CONFIG->isDebug) {
                            $this->app['mailer']->send($message);
                        }
                        $userNotificationRepo->markEmailed($userNotification);

                    }
                }
            }

            $uwgb = new UserWatchesGroupRepositoryBuilder();
            $uwgb->setGroup($group);
            foreach ($uwgb->fetchAll() as $userWatchesGroup) {
                $user = $userRepo->loadByID($userWatchesGroup->getUserAccountId());
                if ($userWatchesGroup->getIsWatching()) {
                    /// Notification Class
                    $userNotification = $userNotificationType->getNewNotification($user, $site);
                    $userNotification->setImport($import);
                    $userNotification->setGroup($group);

                    ////// Save Notification Class
                    $userNotificationRepo->create($userNotification);

                    ////// Send Email
                    if ($userNotification->getIsEmail()) {
                        $userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
                        $userWatchesGroupStop = $userWatchesGroupStopRepository->getForUserAndGroup($user, $group);

                        $message = \Swift_Message::newInstance();
                        $message->setSubject("Please confirm this is still valid: ".$import->getTitle());
                        $message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
                        $message->setTo($user->getEmail());

                        $messageText = $this->app['twig']->render('email/importExpired.watchesGroup.txt.twig', array(
                            'user'=>$user,
                            'import'=>$import,
                            'stopCode'=>$userWatchesGroupStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                            'group'=>$group,
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importExpired.watchesGroup.txt', $messageText);
                        $message->setBody($messageText);

                        $messageHTML = $this->app['twig']->render('email/importExpired.watchesGroup.html.twig', array(
                            'user'=>$user,
                            'import'=>$import,
                            'stopCode'=>$userWatchesGroupStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                            'group'=>$group,
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importExpired.watchesGroup.html', $messageHTML);
                        $message->addPart($messageHTML,'text/html');

                        if (!$CONFIG->isDebug) {
                            $this->app['mailer']->send($message);
                        }
                        $userNotificationRepo->markEmailed($userNotification);
                    }
                }
            }

        } else {
            $lastRunDate = $importURLRepo->getLastRunDateForImportURL($import);
            $nowDate = \TimeSource::getDateTime();
            if (!$lastRunDate || ($lastRunDate->getTimestamp() < $nowDate->getTimestamp() - $CONFIG->importURLSecondsBetweenImports)) {
                $this->logVerbose( " - importing");
                $runner = new ImportRunner($this->app);
                $runner->go($import);
            } else {
                $this->logVerbose(" - already done on ".$lastRunDate->format("c") );
            }
        }


    }
	
}

