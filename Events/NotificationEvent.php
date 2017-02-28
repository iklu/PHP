<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 15.11.2016
 * Time: 15:59
 */

namespace Acme\DataBundle\Events;


use Acme\DataBundle\Model\PushNotifications\NotificationsManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class NotificationEvent extends MainEvent
{
    private $notificationsManager;

    public function __construct(NotificationsManagerInterface $notificationsManager,  Request $request)
    {
        parent::__construct($request);
        $this->notificationsManager = $notificationsManager;
    }

    /**
     * @return NotificationsManagerInterface
     */
    public function getNotificationsManager()
    {
        return $this->notificationsManager;
    }
}