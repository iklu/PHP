<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 13.07.2016
 * Time: 12:14
 */

namespace Acme\DataBundle\Events;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainEvent extends Event
{
    protected $request;
    protected $response;
    protected $httpStatus;
    protected $status;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->status = false;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function setStatus($status)
    {
        $this->httpStatus = $status;
    }

    /**
     * @return Response
     */
    public function getStatus()
    {
        return $this->httpStatus;
    }

    public function setStopped($status){
        return $this->status = $status;
    }

    public function stopped(){
        return $this->status;
    }
}