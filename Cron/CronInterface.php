<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 03.11.2016
 * Time: 14:58
 */

namespace Acme\DataBundle\Model\Cron;


interface CronInterface
{
    public function add($csvFile, $logFile, $params);
}