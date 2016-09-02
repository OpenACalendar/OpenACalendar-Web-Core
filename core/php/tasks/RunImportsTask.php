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

		$iurlBuilder = new ImportRepositoryBuilder($this->app);

		foreach($iurlBuilder->fetchAll() as $import) {
            $this->runImport($import);
        }
        return array('result'=>'ok');

    }

    public function runImport(ImportModel $import) {

        $siteRepo = new SiteRepository($this->app);
        $siteFeatureRepository = new SiteFeatureRepository($this->app);
        $groupRepo = new GroupRepository($this->app);
        $importURLRepo = new ImportRepository($this->app);
        $userRepo = new UserAccountRepository($this->app);
        $userWatchesSiteStopRepository = new UserWatchesSiteStopRepository($this->app);
        $userWatchesGroupStopRepository = new UserWatchesGroupStopRepository($this->app);
        $userAccountGeneralSecurityKeyRepository = new UserAccountGeneralSecurityKeyRepository($this->app);
        $userNotificationRepo = new UserNotificationRepository($this->app);

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

            $uwsb = new UserWatchesSiteRepositoryBuilder($this->app);
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
                        $message->setFrom(array($this->app['config']->emailFrom => $this->app['config']->emailFromName));
                        $message->setTo($user->getEmail());

                        $templateData =array(
                            'user'=>$user,
                            'import'=>$import,
                            'stopCode'=>$userWatchesSiteStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                            'group'=>$group,
                        );

                        $messageSubject = $this->app['twig']->render('email/importExpired.watchesSite.subject.twig', $templateData);
                        if ($this->app['config']->isDebug) {
                            file_put_contents('/tmp/importExpired.watchesSite.subject', $messageSubject);
                        }
                        $message->setSubject(trim($messageSubject));

                        $messageText = $this->app['twig']->render('email/importExpired.watchesSite.txt.twig', $templateData);
                        if ($this->app['config']->isDebug) {
                            file_put_contents('/tmp/importExpired.watchesSite.txt', $messageText);
                        }
                        $message->setBody($messageText);

                        $messageHTML = $this->app['twig']->render('email/importExpired.watchesSite.html.twig', $templateData);
                        if ($this->app['config']->isDebug) {
                            file_put_contents('/tmp/importExpired.watchesSite.html', $messageHTML);
                        }
                        $message->addPart($messageHTML,'text/html');

                        if ($this->app['config']->actuallySendEmail) {
                            $this->app['mailer']->send($message);
                        }
                        $userNotificationRepo->markEmailed($userNotification);

                    }
                }
            }

            $uwgb = new UserWatchesGroupRepositoryBuilder($this->app);
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
                        $message->setFrom(array($this->app['config']->emailFrom => $this->app['config']->emailFromName));
                        $message->setTo($user->getEmail());

                        $templateData = array(
                            'user'=>$user,
                            'import'=>$import,
                            'stopCode'=>$userWatchesGroupStop->getAccessKey(),
                            'generalSecurityCode'=>$userAccountGeneralSecurityKey->getAccessKey(),
                            'group'=>$group,
                        );

                        $messageSubject = $this->app['twig']->render('email/importExpired.watchesGroup.subject.twig', $templateData);
                        if ($this->app['config']->isDebug) {
                            file_put_contents('/tmp/importExpired.watchesGroup.subject', $messageSubject);
                        }
                        $message->setSubject(trim($messageSubject));

                        $messageText = $this->app['twig']->render('email/importExpired.watchesGroup.txt.twig', $templateData);
                        if ($this->app['config']->isDebug) {
                            file_put_contents('/tmp/importExpired.watchesGroup.txt', $messageText);
                        }
                        $message->setBody($messageText);

                        $messageHTML = $this->app['twig']->render('email/importExpired.watchesGroup.html.twig', $templateData);
                        if ($this->app['config']->isDebug) {
                            file_put_contents('/tmp/importExpired.watchesGroup.html', $messageHTML);
                        }
                        $message->addPart($messageHTML,'text/html');

                        if ($this->app['config']->actuallySendEmail) {
                            $this->app['mailer']->send($message);
                        }
                        $userNotificationRepo->markEmailed($userNotification);
                    }
                }
            }

        } else {
            $lastRunDate = $importURLRepo->getLastRunDateForImportURL($import);
            $nowDate = \TimeSource::getDateTime();
            if (!$lastRunDate || ($lastRunDate->getTimestamp() < $nowDate->getTimestamp() - $this->app['config']->importSecondsBetweenImports)) {
                $this->logVerbose( " - importing");
                $runner = new ImportRunner($this->app);
                $runner->go($import);
            } else {
                $this->logVerbose(" - already done on ".$lastRunDate->format("c") );
            }
        }


    }
	
}

