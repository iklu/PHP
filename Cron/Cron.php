<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 03.11.2016
 * Time: 15:00
 */

namespace Acme\DataBundle\Model\Cron;

use Acme\DataBundle\Model\Utility\FilesUtility;


abstract class Cron
{
    /**
     * @var
     */
    protected $notification;

    /**
     * @var
     */
    protected $container;

    /**
     * @var
     */
    protected $em;

    /**
     * @var string
     */
    protected $logFile;

    /**
     * @var string
     */
    protected $csvFile;

    /**
     * StoresCenterLevelService constructor.
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * @param $csvFile
     * @param $logFile
     * @return mixed
     */
    public function getCsvImportData($csvFile, $logFile){

        set_time_limit(0);

        if($logFile == "") {
            $logFile = str_replace(".csv", "", $csvFile)."-cron.txt";
        }

        $this->logFile = $this->container->getParameter('project')['site_path']  . $this->container->getParameter('project')['upload_dir_documents']. $logFile . date("Y-m-d");
        $this->csvFile =  $this->container->getParameter('project')['site_path'] . $this->container->getParameter('project')['upload_dir_documents']. $csvFile;

        $csvContent = FilesUtility::getCsvContent($this->csvFile, $this->logFile);

        return $csvContent;
    }
}