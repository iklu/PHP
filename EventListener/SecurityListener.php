<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 02.09.2016
 * Time: 15:24
 */

namespace Acme\DataBundle\EventListener;

use Acme\ApiBundle\Controller\FormsController;
use Acme\ApiBundle\Controller\SecurityExceptionController;
use Acme\DataBundle\Model\Utility\ApiResponse;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use \Symfony\Component\HttpKernel\Event\GetResponseEvent;


class SecurityListener
{
    private $tokens;
    private $route;
    private $container;

    public function __construct( $route, $container)
    {
        $this->route  = $route;
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        /** @var get $controller */
        $controller = $event->getController();
        if ($controller[0] instanceof FormsController && $event->getRequest()->getMethod()=="POST") {

            //forbidden email keywords
            $forbidden = array(
                'sample',
                'email',
                'test');

            /** @var get all the requests $request */
            $request = Request::createFromGlobals();

            //mix get params with post params in POST
            $email = array_merge($request->query->all(), $request->request->all());

            //check for key "email"
            if(array_key_exists('email', $email)){

                //preg match
                foreach($forbidden as $value){
                    if (preg_match("/".$value."/" , $email['email'])){

                        /** Redirect the controller to a fake response */
                        $event->setController(array(new SecurityExceptionController(), 'catchSecurityExceptionAction' ));

                    }
                }

                //check for key "contactEmail"
            } elseif(array_key_exists('contactEmail', $email)) {
                //preg match
                foreach($forbidden as $value){
                    if (preg_match("/".$value."/" , $email['contactEmail'])){

                        /** Redirect the controller to a fake response */
                        $event->setController(array(new SecurityExceptionController(), 'catchSecurityExceptionAction' ));
                    }
                }
            }
        }
    }
}