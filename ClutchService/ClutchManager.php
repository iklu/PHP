<?php

namespace Acme\DataBundle\Model\ClutchService;

use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 26.09.2016
 * Time: 15:56
 */
abstract class ClutchManager
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var
     */
    protected $request;

    /**
     * @var array
     */
    protected $api = array();

    /**
     * ClutchManager constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $mobile = $this->isMobile();
        $this->setClutchParameters($mobile);
    }

    /**
     * @return object
     */
    public function getRequest(){
        return $this->container->get("request");
    }

    /**
     * @return bool
     */
    public function isMobile(){

        $mobile = $this->getRequest()->get("mobile");

        if($mobile == 1){
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param bool $mobile
     */
    public function setClutchParameters($mobile=false){

        if($mobile == true) {
            $this->api = $this->getClutchMobileParameters();
        } else {
            $this->api = $this->getClutchParameters();
        }
    }

    public function getClutchMobileParameters(){

        $mobileParameters = array(

            "api_url"       => $this->container->getParameter("clutch_mobile")["api_url"],
            "api_service"   => $this->container->getParameter("clutch_mobile")["api_service"],
            "api_port"      => $this->container->getParameter("clutch_mobile")["api_port"],
            "api_key"       => $this->container->getParameter("clutch_mobile")["api_key"],
            "api_secret"    => $this->container->getParameter("clutch_mobile")["api_secret"],
            "brand"         => $this->container->getParameter("clutch_mobile")["brand"],
            "location"      => $this->container->getParameter("clutch_mobile")["location"],
            "terminal"      => $this->container->getParameter("clutch_mobile")["terminal"],

        );
        return $mobileParameters;
    }

    public function getClutchParameters(){

        $parameters = array(
            "api_url"       => $this->container->getParameter("clutch")["api_url"],
            "api_service"   => $this->container->getParameter("clutch")["api_service"],
            "api_port"      => $this->container->getParameter("clutch")["api_port"],
            "api_key"       => $this->container->getParameter("clutch")["api_key"],
            "api_secret"    => $this->container->getParameter("clutch")["api_secret"],
            "brand"         => $this->container->getParameter("clutch")["brand"],
            "location"      => $this->container->getParameter("clutch")["location"],
            "terminal"      => $this->container->getParameter("clutch")["terminal"],
        );
        return $parameters;
    }
}
