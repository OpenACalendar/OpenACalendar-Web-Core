<?php

namespace tasks;


use models\ImportURLModel;
use repositories\builders\ImportURLRepositoryBuilder;
use repositories\SiteFeatureRepository;
use repositories\SiteRepository;
use repositories\GroupRepository;
use repositories\ImportURLRepository;
use import\ImportURLRunner;
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
class RunImportURLsTask extends \BaseTask  {



	public function getExtensionId()
	{
		return 'org.openacalendar';
	}

	public function getTaskId()
	{
		return 'RunImportURLs';
	}

	public function getShouldRunAutomaticallyNow() {
		return $this->app['config']->taskRunImportURLsAutomaticUpdateInterval > 0 &&
		$this->getLastRunEndedAgoInSeconds() > $this->app['config']->taskRunImportURLsAutomaticUpdateInterval;
	}

	protected function run() {

		$iurlBuilder = new ImportURLRepositoryBuilder();

		foreach($iurlBuilder->fetchAll() as $importURL) {
            $this->runImportURL($importURL);
        }
        return array('result'=>'ok');

    }

    public function runImportURL(ImportURLModel $importURL) {
        global $CONFIG;

        $siteRepo = new SiteRepository();
        $siteFeatureRepository = new SiteFeatureRepository($this->app);
        $groupRepo = new GroupRepository();
        $importURLRepo = new ImportURLRepository();
        $userRepo = new UserAccountRepository();
        $userWatchesSiteStopRepository = new UserWatchesSiteStopRepository();
        $userWatchesGroupStopRepository = new UserWatchesGroupStopRepository();
        $userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository();
        $userNotificationRepo = new UserNotificationRepository();

        /** @var usernotifications/UpcomingEventsUserNotificationType **/
        $userNotificationType = $this->app['extensions']->getCoreExtension()->getUserNotificationType('ImportURLExpired');

        $site = $siteRepo->loadById($importURL->getSiteID());
        $importerFeature = $siteFeatureRepository->doesSiteHaveFeatureByExtensionAndId($site, 'org.openacalendar','Importer');
        $group = $groupRepo->loadById($importURL->getGroupId());

        $this->logVerbose(" ImportURL ".$importURL->getId()." ".$importURL->getTitle()." Site ".$site->getTitle());

        if ($site->getIsClosedBySysAdmin()) {
            $this->logVerbose( " - site closed by sys admin");
        } else if (!$importerFeature) {
            $this->logVerbose( " - site feature disabled");
        } else if (!$group) {
            $this->logVerbose( " - no group - this should be impossible");
        } else if ($group->getIsDeleted()) {
            $this->logVerbose( " - group deleted");
        } else if ($importURL->getExpiredAt()) {
            $this->logVerbose( " - expired");
        } else if (!$importURL->getIsEnabled()) {
            $this->logVerbose( " - not enabled");
        } else if ($importURL->isShouldExpireNow()) {
            $this->logVerbose( " - expiring" );
            $importURLRepo->expire($importURL);

            configureAppForSite($site);

            $uwsb = new UserWatchesSiteRepositoryBuilder();
            $uwsb->setSite($site);
            foreach ($uwsb->fetchAll() as $userWatchesSite) {
                $user = $userRepo->loadByID($userWatchesSite->getUserAccountId());
                if ($userWatchesSite->getIsWatching()) {

                    /// Notification Class
                    $userNotification = $userNotificationType->getNewNotification($user, $site);
                    $userNotification->setImportURL($importURL);
                    $userNotification->setGroup($group);

                    ////// Save Notification Class
                    $userNotificationRepo->create($userNotification);

                    ////// Send Email
                    if ($userNotification->getIsEmail()) {

                        configureAppForUser($user);
                        $userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
                        $userWatchesSiteStop = $userWatchesSiteStopRepository->getForUserAndSite($user, $site);

                        $message = \Swift_Message::newInstance();
                        $message->setSubject("Please confirm this is still valid: ".$importURL->getTitle());
                        $message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
                        $message->setTo($user->getEmail());

                        $messageText = $this->app['twig']->render('email/importURLExpired.watchesSite.txt.twig', array(
                            'user'=>$user,
                            'importurl'=>$importURL,
                            'stopCode'=>$userWatchesSiteStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesSite.txt', $messageText);
                        $message->setBody($messageText);

                        $messageHTML = $this->app['twig']->render('email/importURLExpired.watchesSite.html.twig', array(
                            'user'=>$user,
                            'importurl'=>$importURL,
                            'stopCode'=>$userWatchesSiteStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesSite.html', $messageHTML);
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
                    $userNotification->setImportURL($importURL);
                    $userNotification->setGroup($group);

                    ////// Save Notification Class
                    $userNotificationRepo->create($userNotification);

                    ////// Send Email
                    if ($userNotification->getIsEmail()) {
                        $userAccountGeneralSecurityKey = $userAccountGeneralSecurityKeyRepository->getForUser($user);
                        $userWatchesGroupStop = $userWatchesGroupStopRepository->getForUserAndGroup($user, $group);

                        $message = \Swift_Message::newInstance();
                        $message->setSubject("Please confirm this is still valid: ".$importURL->getTitle());
                        $message->setFrom(array($CONFIG->emailFrom => $CONFIG->emailFromName));
                        $message->setTo($user->getEmail());

                        $messageText = $this->app['twig']->render('email/importURLExpired.watchesGroup.txt.twig', array(
                            'user'=>$user,
                            'importurl'=>$importURL,
                            'stopCode'=>$userWatchesGroupStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                            'group'=>$group,
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesGroup.txt', $messageText);
                        $message->setBody($messageText);

                        $messageHTML = $this->app['twig']->render('email/importURLExpired.watchesGroup.html.twig', array(
                            'user'=>$user,
                            'importurl'=>$importURL,
                            'stopCode'=>$userWatchesGroupStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                            'group'=>$group,
                        ));
                        if ($CONFIG->isDebug) file_put_contents('/tmp/importURLExpired.watchesGroup.html', $messageHTML);
                        $message->addPart($messageHTML,'text/html');

                        if (!$CONFIG->isDebug) {
                            $this->app['mailer']->send($message);
                        }
                        $userNotificationRepo->markEmailed($userNotification);
                    }
                }
            }

        } else {
            $lastRunDate = $importURLRepo->getLastRunDateForImportURL($importURL);
            $nowDate = \TimeSource::getDateTime();
            if (!$lastRunDate || ($lastRunDate->getTimestamp() < $nowDate->getTimestamp() - $CONFIG->importURLSecondsBetweenImports)) {
                $this->logVerbose( " - importing");
                $runner = new ImportURLRunner();
                $runner->go($importURL);
            } else {
                $this->logVerbose(" - already done on ".$lastRunDate->format("c") );
            }
        }


    }
	
}

