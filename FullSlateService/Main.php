<?php

namespace Acme\DataBundle\Model\FullSlateService;
use Acme\DataBundle\AcmeDataEvents;
use Acme\DataBundle\Entity\Appointments;
use Acme\DataBundle\Entity\AppointmentsHasServices;
use Acme\DataBundle\Entity\Stores;
use Acme\DataBundle\Events\FullSlateEvent;
use Acme\DataBundle\Model\Utility\ApiResponse;
use Acme\DataBundle\Model\Utility\Curl;
use Acme\DataBundle\Model\Utility\DataSerializer;
use Acme\DataBundle\Model\Utility\EntitiesUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Utility\StringUtility;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.02.2017
 * Time: 12:40
 */
class Main implements MainInterface
{
    public $params;
    /**
     * @var AppointmentsManagerInterface
     */
    public $manager;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(AppointmentsManagerInterface $manager, $params = [], EventDispatcherInterface $dispatcher)
    {
        $this->manager = $manager;
        $this->params = $params;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param $storeId
     * @param $bookingId
     * @return array
     */
    public function getBooking($storeId, $bookingId) {

        $booking = [];

        /** get the entity Appointments */
        $this->manager->createAppointments('AcmeDataBundle:Appointments');

        $entity = $this->manager->findAppointmentByBookingId($bookingId);

        if (!$entity) {
            return new Notification(false, 'There is no appointment.');
        } elseif ($entity && $entity->getStores()->getStoreId() != $storeId) {
            return new Notification(false, 'There is no appointment.');
        }

        if ($entity){

            $booking = DataSerializer::deserializeWithCamelCaseEntityToArray($entity);

            /** get the entity AppointmentsHasServices services */
            $this->manager->createAppointments('AcmeDataBundle:AppointmentsHasServices');
            $servicesApp = $this->manager->getAppointmentsServicesDetails($booking["id"]);
            if ($servicesApp) {
                $services = array();
                for ($j=0;$j<count($servicesApp);$j++) {
                    $services[$j] = $servicesApp[$j]->getServices()->getTitle();
                }
                $booking['services'] = implode(", ", $services);
            }
        }

        return new Notification(true, $booking);

    }

    /**
     * DELETE
     *
     * @param $storeId
     * @param $bookingId
     * @param Request $request
     * @return array
     */
    public function deleteBooking($storeId, $bookingId, Request $request)
    {
        /** get the entity Appointments */
        $this->manager->createAppointments('AcmeDataBundle:Appointments');

        $entity = $this->manager->findAppointmentByBookingId($bookingId);

        if (!$entity) {
            return new Notification(false, 'There is no appointment.');
        } elseif ($entity && $entity->getStores()->getStoreId() != $storeId) {
            return new Notification(false, 'There is no appointment.');
        } elseif ($entity && $entity->getStatus() == "CANCELED") {
            return new Notification(false, 'There is no appointment.');
        }

        /** @var $event  (inject manager to FullSlateEvent class for managing Appointments entity)*/
        $event = new FullSlateEvent($this->manager, $request, $this->params, $storeId, $bookingId);

        /** Dispatch delete booking from fullSlate event */
        $this->dispatcher->dispatch(AcmeDataEvents::CANCEL_BOOKING_FROM_FULLSLATE, $event);

        if(!$event->stopped()) {
            if ($request->get("delete") == true) {
                /** Dispatch delete booking from fullSlate event */
                $this->dispatcher->dispatch(AcmeDataEvents::DELETE_FROM_APPOINTMENTS_TABLE, $event);
            } else {
                /** Dispatch cancel booking from fullSlate event */
                $this->dispatcher->dispatch(AcmeDataEvents::CANCEL_FROM_APPOINTMENTS_TABLE, $event);
            }
            return new Notification(true, $event->getResponse());
        } else {
            return new Notification(false, $event->getResponse());
        }
    }

    /**
     * GET
     * 
     * Get all bookings from database
     *
     * @param string $storeId
     * @param string $bookingId
     * @param string $userId
     * @param string $email
     * @param bool $upcoming
     * @param Request $request
     * @return array
     */
    public function getBookings($storeId="", $bookingId="", $userId="", $email="", $upcoming=false, Request $request) {

        $appointments = [];
        $bookings = [];

        //set pagination and sorting
        StringUtility::setListingConfigurations($request, $page, $noRecords, $sortField, $sortType);

        /** get the entity Appointments */
        $this->manager->createAppointments('AcmeDataBundle:Appointments');

        if (!empty($email) || !empty($userId) || !empty($bookingId) ) {
            $bookings["bookings"] = $this->manager->getAllAppointments($page, $noRecords, $sortField, $sortType, $storeId, $bookingId, $userId, $email, $upcoming);
        }

        if (!empty($email)) {
            $bookings["noTotal"] = $this->manager->countAppointments($storeId, $bookingId, $userId, $email, $upcoming);
        }

        if ($bookings["bookings"]) {
            //check services
            for ($i=0;$i<count($bookings["bookings"]);$i++) {

                /** get the entity AppointmentsHasServices services */
                $this->manager->createAppointments('AcmeDataBundle:AppointmentsHasServices');
                $servicesApp = $this->manager->getAppointmentsServicesDetails($bookings["bookings"][$i]['id']);
                if ($servicesApp) {
                    $services = array();
                    for ($j=0;$j<count($servicesApp);$j++) {
                        $services[$j] = $servicesApp[$j]->getServices()->getTitle();
                    }
                    $bookings["bookings"][$i]['services'] = implode(", ", $services);
                }

                if (empty($bookings["bookings"][$i]['timezone']) || $bookings["bookings"][$i]['timezone'] == null ) {
                    $bookings["bookings"][$i]['timezone'] = "UTC";
                }
                $storeTime = EntitiesUtility::getTimezone($bookings["bookings"][$i]['timezone']);
                $appointmentDate = new \DateTime($bookings["bookings"][$i]['appointmentDate']->format('Y-m-d H:i:s'));
                //add 1 day delay
                $appointmentDate->add(new \DateInterval('P1D'));
                //format date
                $fullDate = $bookings["bookings"][$i]['appointmentDate'];
                $bookings["bookings"][$i]['appointmentDate'] = $fullDate->format('m/d/Y');
                $bookings["bookings"][$i]['appointmentHours'] = $fullDate->format('g:i a');

                if ($upcoming == true ) {
                    if (($appointmentDate > $storeTime['store_time']) && $bookings["bookings"][$i]["status"] != "CANCELED"){
                        $appointments["bookings"][] = $bookings["bookings"][$i];
                        $appointments["noTotal"] = $bookings["noTotal"];
                    }

                } else {
                    $appointments["bookings"][] = $bookings["bookings"][$i];
                    $appointments["noTotal"] = $bookings["noTotal"];
                }
            }
        } else {
            $appointments = $bookings;
        }

        return $appointments;
    }

    /**
     * POST
     *
     * @param Request $request
     * @return Notification
     */
    public function addBooking(Request $request) {

        /** get the entity Stores */
        $this->manager->createAppointments('AcmeDataBundle:Stores');

        /** @var $event  (inject manager to FullSlateEvent class for managing Appointments entity)*/
        $event = new FullSlateEvent($this->manager, $request, $this->params);

        /** Dispatch check booking details like store */
        $this->dispatcher->dispatch(AcmeDataEvents::CHECK_IF_BOOKING_IS_VALID, $event);

        if (!$event->stopped()) {
            /** get the entity Appointments */
            $this->manager->createAppointments('AcmeDataBundle:Appointments');

            /** Dispatch add booking to fullSlate event */
            $this->dispatcher->dispatch(AcmeDataEvents::ADD_BOOKING_TO_FULLSLATE, $event);
            
            if(!$event->stopped()) {
                /** Dispatch add booking to fullSlate event */
                $this->dispatcher->dispatch(AcmeDataEvents::SAVE_TO_APPOINTMENTS_TABLE, $event);

                /** Failed to save to database emit delete booking */
                if($event->stopped()){

                    /** @var $event  (inject manager to FullSlateEvent class for managing Appointments entity)*/
                    $event = new FullSlateEvent($this->manager, $request, $this->params, trim($request->get("storeId")), $event->getResponse()["id"]);

                    /** Dispatch delete booking from fullSlate event */
                    $this->dispatcher->dispatch(AcmeDataEvents::CANCEL_BOOKING_FROM_FULLSLATE, $event);

                    $event->setStatus(Codes::HTTP_INTERNAL_SERVER_ERROR);
                    $event->setResponse("The appointment can't be scheduled due the server error.");

                    return new Notification(false, $event->getResponse(), $event->getStatus());
                } else {
                    /** Dispatch delete booking from fullSlate event */
                    $this->dispatcher->dispatch(AcmeDataEvents::APPOINTMENT_SUCCESS, $event);

                    return new Notification(false, $event->getResponse(), $event->getStatus());
                }
            } else {
                return new Notification(false, $event->getResponse(), $event->getStatus());
            }

        } else {
            return new Notification(false, $event->getResponse(), $event->getStatus());
        }

    }
}