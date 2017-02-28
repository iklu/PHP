<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 22.02.2017
 * Time: 15:56
 */

namespace Acme\DataBundle\Events;


use Acme\DataBundle\Model\FullSlateService\AppointmentsInterface;
use Acme\DataBundle\Model\FullSlateService\AppointmentsManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class FullSlateEvent extends MainEvent
{

    /**
     * @var AppointmentsManagerInterface
     */
    private $appointmentsManager;

    private $params = [];
    /**
     * @var integer
     */
    private $storeId;
    /**
     * @var string
     */
    private $bookingId;

    public function __construct(AppointmentsManagerInterface $appointmentsManager,  Request $request, $params=[], $storeId = '', $bookingId = '')
    {
        parent::__construct($request);
        $this->appointmentsManager = $appointmentsManager;
        $this->params = $params;
        $this->storeId = $storeId;
        $this->bookingId = $bookingId;
    }

    /**
     * @return AppointmentsInterface
     */
    public function getAppointmentsManager()
    {
        return $this->appointmentsManager;
    }

    /**
     * @return array
     */
    public function getParams(){
        return $this->params;
    }

    /**
     * @return string
     */
    public function getBookingId(){
        return $this->bookingId;
    }

    /**
     * @return int
     */
    public function getStoreId(){
        return $this->storeId;
    }
}