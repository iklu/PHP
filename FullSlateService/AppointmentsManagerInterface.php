<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 22.02.2017
 * Time: 16:00
 */

namespace Acme\DataBundle\Model\FullSlateService;


interface AppointmentsManagerInterface
{
    public function createAppointments($class);
    public function deleteAppointment(AppointmentsInterface $appointment);
    public function findAppointmentByBookingId($bookingId);
    public function countAppointments($storeId , $bookingId , $userId , $email, $upcoming);
    public function getAllAppointments( $page, $noRecords, $sortField, $sortType, $storeId, $bookingId, $userId, $email, $upcoming);
    public function getAppointmentsServicesDetails($id);
    public function getEntity();
}