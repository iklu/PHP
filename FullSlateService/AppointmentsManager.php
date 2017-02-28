<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 22.02.2017
 * Time: 15:59
 */

namespace Acme\DataBundle\Model\FullSlateService;
use Acme\DataBundle\Entity\Services;
use Acme\DataBundle\Entity\Stores;
use Doctrine\Common\Persistence\ObjectManager;


class AppointmentsManager implements AppointmentsManagerInterface
{
    protected $objectManager;
    protected $class;
    protected $repository;
    protected $appointments;

    /**
     * RewardsManager constructor.
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->objectManager = $om;
    }

    /**
     * Returns an empty appointments instance
     *
     * @return AppointmentsManagerInterface
     */
    public function createAppointments($class)
    {
        $this->class = $class;
        $this->repository =  $this->objectManager->getRepository($class);

        $metadata =  $this->objectManager->getClassMetadata($class);
        $this->class = $metadata->getName();

        $class = $this->getClass();
        $this->appointments = new $class;

        return $this->appointments;
    }

    /**
     * Returns the transaction
     *
     * @return mixed
     */
    public function transaction(){
        return   $this->objectManager->getConnection();
    }

    /**
     * {@inheritDoc}
     */
    public function getAllAppointments($page, $noRecords, $sortField, $sortType, $storeId, $bookingId, $userId, $email, $upcoming)
    {
        return $this->repository->getBookings($page, $noRecords, $sortField, $sortType, $storeId, $bookingId, $userId, $email, $upcoming);
    }

    /**
     * @param AppointmentsInterface $id
     * @return mixed
     */
    public function getAppointmentsServicesDetails($id){
        return $this->repository->findByAppointments($id);
    }

    /**
     * {@inheritDoc}
     */
    public function countAppointments($storeId, $bookingId, $userId, $email, $upcoming)
    {
        return $this->repository->getBookingsCount($storeId, $bookingId, $userId, $email,  $upcoming);
    }

    /**
     * Find a user by registered user id
     *
     * @param integer $userId
     *
     * @return AppointmentsManagerInterface
     */
    public function findUserById($userId) {
        return $this->repository->findById($userId);
    }

    /**
     * Find a store by storeId
     *
     * @param integer $storeId
     *
     * @return Stores
     */
    public function findByStoreId($storeId) {
        return $this->repository->findOneByStoreId($storeId);
    }

    /**
     * Find a service by title
     *
     * @param string $title
     *
     * @return Services
     */
    public function findServiceByTitle($title) {
        return $this->repository->findOneByTitle($title);
    }


    /**
     * {@inheritDoc}
     */
    public function deleteAppointment(AppointmentsInterface $appointment) {

        $this->objectManager->remove($appointment);
        $this->objectManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAppointmentHasServices(AppointmentsInterface $appointment)
    {
        $servicesApp = $this->repository->findByAppointments($appointment->getId());
        if($servicesApp) {
            foreach($servicesApp as $srv){
                $this->objectManager->remove($srv);
                $this->objectManager->flush();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function findRewardByCriteria(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findAppointmentByBookingId($bookingId)
    {
        return $this->repository->findOneByfullSlateId($bookingId);
    }

    /**
     * Updates a reward.
     *
     * @param AppointmentsInterface $appointments
     * @param Boolean       $andFlush Whether to flush the changes (default true)
     */
    public function updateAppointments(AppointmentsInterface $appointments, $andFlush = true)
    {
        $this->objectManager->persist($appointments);
        if ($andFlush) {
            $this->objectManager->flush();
        }
    }

    /**
     * @return mixed
     */
    public function getEntity(){
        return $this->appointments;
    }
}