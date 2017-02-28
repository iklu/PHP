<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 22.02.2017
 * Time: 15:54
 */

namespace Acme\DataBundle\EventListener;

use Acme\DataBundle\AcmeDataEvents;
use Acme\DataBundle\Events\FullSlateEvent;
use Acme\DataBundle\Model\FullSlateService\FullSlate;
use Acme\DataBundle\Model\FullSlateService\Helper;
use Acme\DataBundle\Model\Utility\ApiResponse;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BookingAppointmentListener implements EventSubscriberInterface
{
    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            AcmeDataEvents::CHECK_IF_BOOKING_IS_VALID => 'checkIfBookingIsValid',
            AcmeDataEvents::ADD_BOOKING_TO_FULLSLATE => 'addBookingToFullSlate',
            AcmeDataEvents::SAVE_TO_APPOINTMENTS_TABLE => 'saveToAppointmentsTable',
            AcmeDataEvents::CANCEL_BOOKING_FROM_FULLSLATE => 'cancelBookingFromFullSlate',
            AcmeDataEvents::CANCEL_FROM_APPOINTMENTS_TABLE => 'cancelFromAppointmentsTable',
            AcmeDataEvents::DELETE_FROM_APPOINTMENTS_TABLE => 'deleteFromAppointmentsTable',
            AcmeDataEvents::APPOINTMENT_SUCCESS => 'appointmentSuccess',
        );
    }

    /**
     * CANCEL_BOOKING_FROM_FULLSLATE
     *
     * Accepts AppointmentManagerInterface
     *
     * @param FullSlateEvent $event
     * @return mixed
     */
    public function cancelBookingFromFullSlate(FullSlateEvent $event) {
        $booking = FullSlate::deleteFullSlateBooking($event->getStoreId(), $event->getBookingId(), $event->getParams());
        if (isset($booking["response"]["deleted"]) && isset($booking["response"]["deleted"]) == true) {
            $event->setResponse($booking);
        } elseif (isset($booking["status"]) && $booking["status"] != 200) {
            if (isset($booking["response"]["errorMessage"])) {
                $event->setResponse($booking["response"]["errorMessage"]);
                $event->setStopped(true);
            }
            $event->setStopped(true);
        }
        return $event;
    }


    /**
     * DELETE_FROM_APPOINTMENTS_TABLE
     *
     * Accepts AppointmentManagerInterface
     *
     * @param FullSlateEvent $event
     * @return mixed
     */
    public function deleteFromAppointmentsTable(FullSlateEvent $event) {

        $manager = $event->getAppointmentsManager();
        $manager->transaction()->beginTransaction();
        try{
            $booking = $manager->findAppointmentByBookingId($event->getBookingId());

            if($booking){
                $manager->createAppointments('AcmeDataBundle:AppointmentsHasServices');
                $manager->deleteAppointmentHasServices($booking);
                $manager->createAppointments('AcmeDataBundle:Appointments');
                $manager->deleteAppointment($booking);
            }
            //commit account creation
            $manager->transaction()->commit();

        } catch (\Exception $e) {
            $manager->transaction()->rollback();
            $manager->transaction()->close();
            $event->setStopped(true);
            $event->setResponse("There was an error deleting the booking.");
        }

        return $event;
    }


    /**
     * CANCEL_FROM_APPOINTMENTS_TABLE
     *
     * Accepts AppointmentManagerInterface
     *
     * @param FullSlateEvent $event
     * @return mixed
     */
    public function cancelFromAppointmentsTable(FullSlateEvent $event) {

        $manager = $event->getAppointmentsManager();
        $manager->transaction()->beginTransaction();
        try{
            $booking = $manager->findAppointmentByBookingId($event->getBookingId());

            if($booking){
                $booking->setStatus("CANCELED");
                $manager->updateAppointments($booking, true);
            }
            //commit account creation
            $manager->transaction()->commit();

        } catch (\Exception $e) {
            $manager->transaction()->rollback();
            $manager->transaction()->close();
            $event->setStopped(true);
            $event->setResponse("There was an error deleting the booking.");
        }

        return $event;
    }


    /**
     * CHECK_IF_BOOKING_IS_VALID
     *
     *  Accepts AppointmentManagerInterface
     *
     * @param FullSlateEvent $event
     * @return FullSlateEvent
     */
    public function checkIfBookingIsValid(FullSlateEvent $event) {

        $store = $event->getAppointmentsManager()->findByStoreId($event->getRequest()->get("storeId"));
        //store not found
        if (!$store) {
            $event->setResponse("Store not found.");
            $event->setStopped(true);
            return $event;
        }

        if ($store && !$store->getHasFullSlate()) {
            $event->setResponse('Store doesn\'t have Full Slate.');
            $event->setStopped(true);
            return $event;
        }
        $event->setResponse($store);
        return $event;
    }

    /**
     * ADD_BOOKING_TO_FULLSLATE
     *
     * Accepts AppointmentManagerInterface
     *
     * @param FullSlateEvent $event
     * @return FullSlateEvent
     */
    public function addBookingToFullSlate(FullSlateEvent $event) {

        $request = $event->getRequest();
        $store = $event->getResponse();

        $appointment = FullSlate::saveFullSlateAppointment($request->get("storeId"), Helper::postValues($request, $store), $event->getParams());

        $arrayIds = array(1541, 1257, 1490, 1652, 1669, 1699, 1781, 1800, 1818, 1819, 1837, 1840, 188, 191,
            1917, 1944, 1954, 1969, 1970, 2012, 2067, 2085, 2098, 2115, 214, 2150, 2158, 2160,
            2199, 225, 2253, 2256, 2275, 2302, 2338, 2382, 2443, 2473, 257, 2617, 282, 359,
            374, 394, 4174, 4236, 426, 437, 4378, 495, 5068, 5082, 530, 571, 584, 592, 60, 851, 897, 933, 938, 940);


        /** Status Code from FullSlate */
        if (isset($appointment["status"]) && $appointment["status"] != 200) {
            /** Response message from FullSlate */
            if (isset($appointment["response"])) {
                /**Fake Status Response message from FullSlate for some stores*/
                if (isset($appointment["response"]["failure"]) && isset($appointment["response"]["failure"]) == 1 && in_array($event->getRequest()->get("storeId"), $arrayIds)) {
                    $event->setResponse($appointment["response"]["errorMessage"]);
                    $event->setStatus(Codes::HTTP_OK);
                    $event->setStopped(true);
                }
                $event->setResponse($appointment["response"]);
                $event->setStatus($appointment["status"]);
                $event->setStopped(true);
                return $event;
            } else {
                /**No Response message from FullSlate */
                $event->setResponse('Full Slate API error');
                $event->setStatus(Codes::HTTP_INTERNAL_SERVER_ERROR);
                $event->setStopped(true);
                return $event;
            }
            /** Response OK from FullSlate */
        } elseif(isset($appointment["status"]) && $appointment["status"] == Codes::HTTP_OK && isset($appointment["response"])) {
            $event->setResponse($appointment["response"]);
            $event->setStatus(Codes::HTTP_OK);
            $event->setStopped(false);
            return $event;
        /**No Status from FullSlate */
        } else {
            $event->setResponse('Full Slate API error');
            $event->setStatus(Codes::HTTP_INTERNAL_SERVER_ERROR);
            $event->setStopped(true);
            return $event;
        }
    }


    /**
     * SAVE_TO_APPOINTMENTS_TABLE
     *
     * Accepts AppointmentManagerInterface
     *
     * @param FullSlateEvent $event
     * @return FullSlateEvent
     */
    public function saveToAppointmentsTable(FullSlateEvent $event) {

        $bookingDetails = $event->getResponse();

        if (!isset($bookingDetails["id"])) {
            $event->setStopped(true);
        }

        $request = $event->getRequest();

        $manager = $event->getAppointmentsManager();
        $manager->transaction()->beginTransaction();
        try{

            $manager->createAppointments('AcmeDataBundle:Appointments');
            $appointment = $manager->getEntity();

            $appointment->setFullSlateId($bookingDetails["id"]);
            $appointment->setFirstName(trim($request->get('firstName')));
            $appointment->setLastName(trim($request->get('lastName')));
            $appointment->setEmail(trim($request->get('email')));
            $appointment->setVehicleMake(trim($request->get('vehicleMake')));
            $appointment->setVehicleModel(trim($request->get('vehicleModel')));
            $appointment->setVehicleYear(trim($request->get('vehicleYear')));
            $appointment->setAppointmentDate(new \DateTime(date("Y-m-d H:i:s", strtotime(trim($request->get('dateTime'))))));
            $appointment->setComments(trim($request->get('comments')));
            $appointment->setPaid(trim($request->get('paid')));
            if (trim($request->get('vehicleDropoff')))
                $appointment->setVehicleDropoff(trim($request->get('vehicleDropoff')));
            if (trim($request->get('waitForCar')))
                $appointment->setWaitForCar(trim($request->get('waitForCar')));
            if (trim($request->get('textReminderSMS')))
                $appointment->setTextReminderSMS(trim($request->get('textReminderSMS')));
            $appointment->setPhone(trim($request->get('phone')));


            $manager->createAppointments('AcmeDataBundle:Stores');
            $appointment->setStores($manager->findByStoreId(trim($request->get("storeId"))));
            if (trim($request->get('userId'))) {
                $manager->createAppointments('AcmeDataBundle:Users');
                $user = $manager->findUserById(trim($request->get('userId')));
                if ($user)
                    $appointment->setUsers($user);
            }

            $manager->createAppointments('AcmeDataBundle:Appointments');
            $manager->updateAppointments($appointment, true);

            //appointments has services
            $services = explode("*", trim($request->get('servicesNames')));
            for ($i = 0; $i < count($services); $i++) {
                $manager->createAppointments('AcmeDataBundle:Services');
                $entityService = $manager->findServiceByTitle($services[$i]);
                if ($entityService) {
                    $manager->createAppointments('AcmeDataBundle:AppointmentsHasServices');
                    $entityAHS = $manager->getEntity();
                    $entityAHS->setAppointments($appointment);
                    $entityAHS->setServices($entityService);
                    $manager->updateAppointments($entityAHS, true);
                }
            }

            //commit account creation
            $manager->transaction()->commit();

        } catch (\Exception $e) {
            $manager->transaction()->rollback();
            $manager->transaction()->close();
            $event->setStopped(true);
//            $event->setStatus(Codes::HTTP_INTERNAL_SERVER_ERROR);
//            $event->setResponse($e->getMessage());
        }

        return $event;
    }

    /**
     * APPOINTMENT_SUCCESS
     *
     * Accepts AppointmentManagerInterface
     *
     * @param FullSlateEvent $event
     * @return FullSlateEvent
     */
    public function appointmentSuccess(FullSlateEvent $event) {
        $event->setResponse("Appointment successfully added.");
        $event->setStatus(Codes::HTTP_OK);
        return $event;
    }
}