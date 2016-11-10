<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 10.11.2016
 * Time: 16:13
 */

namespace Acme\DataBundle\Model\Cron;


class ImportScript
{
    public function __construct( $csvFile,  $logFile, $params=array(),CronInterface $strategy )
    {
        $this->importStrategy = $strategy;
        $this->csvFile = $csvFile;
        $this->logFile = $logFile;
        $this->params = $params;
    }

    public function run() {
        return $this->importStrategy->add($this->csvFile, $this->logFile, $this->params);
    }
}