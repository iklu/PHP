<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.02.2017
 * Time: 12:43
 */

namespace Acme\DataBundle\Model\FullSlateService;


use Symfony\Component\HttpFoundation\Request;

interface MainInterface
{
    public function getBookings($storeId, $bookingId, $userId, $email, $upcoming, Request $request);

}