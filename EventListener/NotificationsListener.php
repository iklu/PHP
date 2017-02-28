<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 15.11.2016
 * Time: 15:57
 */

namespace Acme\DataBundle\EventListener;

use Acme\DataBundle\Model\Constants\NotificationStatus;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Acme\DataBundle\AcmeDataEvents;
use Doctrine\ORM\EntityManager;
use Acme\DataBundle\Model\Utility\Curl;

class NotificationsListener implements EventSubscriberInterface
{
    protected $em;

    /**
     * NotificationsListener constructor.
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            AcmeDataEvents::PUSH_NOTIFICATIONS => 'pushNotifications',
        );
    }

    /**
     * PUSH_NOTIFICATIONS
     *
     * Accepts NotificationsManagerInterface
     *
     * @param Event $event
     * @return mixed
     */
    public function pushNotifications(Event $event) {

        $id =array();

        $event->getName();
        $event->getRequest();

        $notifications = $event->getNotificationsManager()->getNotificationsToBePushed();
        
        foreach ($notifications as $notificationId => $pushNotification) {
            //identifier by id for each notification
            $id[] = $notificationId;

            //get notification info
            $notificationsSent = $this->em->getRepository('AcmeDataBundle:Notifications')->find($notificationId);

            //if max retries reached , quit
            if($notificationsSent->getRetries() != $event->getNotificationsManager()->getContainer()->getParameter('push_notifications')['urbanairship_max_retries']) {

                //convert data to json
                $jsonData = json_encode($pushNotification);

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $event->getNotificationsManager()->getContainer()->getParameter('push_notifications')['urbanairship_api_url'].'/push');
                curl_setopt($curl, CURLOPT_USERPWD, $event->getNotificationsManager()->getContainer()->getParameter('push_notifications')['urbanairship_user'].":".$event->getNotificationsManager()->getContainer()->getParameter('push_notifications')['urbanairship_password']);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Accept: application/vnd.urbanairship+json; version=3',
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData),
                ));
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

                $result = curl_exec($curl);
                curl_close($curl);
                $decode = json_decode($result, true);

                if($decode['ok'] == false) {
                    $response[$notificationId]['status'] = 'failed';
                    $notificationsSent->setStatus(NotificationStatus::FAILED);
                } else {
                    $response[$notificationId]['status'] = 'sent';
                    $response[$notificationId]['push_ids'] =  $decode['push_ids'];
                    $notificationsSent->setStatus(NotificationStatus::COMPLETE);
                }

                if($notificationsSent->getStatus()==NotificationStatus::FAILED){
                    $retries = $notificationsSent->getRetries()+1;
                    $notificationsSent->setRetries($retries);
                    $this->em->persist($notificationsSent);
                    $this->em->flush();
                } else {
                    $this->em->persist($notificationsSent);
                    $this->em->flush();
                }
            }
        }

        $response['ids'] = implode(',', $id);
        return $event->setResponse($response);
    }
}