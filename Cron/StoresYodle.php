<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.05.2016
 * Time: 11:09
 */

namespace Acme\DataBundle\Model\Cron;

use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Entity\Stores;
use Acme\DataBundle\Entity\Yodle;


class StoresYodle
{
    /**
     * @var
     */
    protected $notification;

    /**
     * @var
     */
    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function add($csvFile) {
        set_time_limit(0);
        $em = $this->container->get('doctrine')->getManager();
        $file = $this->container->getParameter('project')['site_path'] . $this->container->getParameter('project')['upload_dir_documents'] . 'cron' . date("Y-m-d") . '.txt';
        $localFile = $this->container->getParameter('project')['site_path'] . $this->container->getParameter('project')['upload_dir_documents'] . $csvFile;
        ini_set('auto_detect_line_endings', TRUE);

        $array = $fields = array(); $i = 0;
        $handle = @fopen($localFile, "r");
        if($handle) {
            while(($row = fgetcsv($handle, 4096)) !== FALSE) {
                if(empty($fields)) {
                    $fields = $row;
                    continue;
                }

                foreach($row as $k=>$value) {
                    $array[$i][$fields[$k]] = $value;
                }
                $i++;
            }
            if(!feof($handle)) {
                file_put_contents($file, 'Error: unexpected fgets() fail.' . PHP_EOL, FILE_APPEND);
                exit();
            }
            fclose($handle);
        }
        $finalData = StringUtility::changeArrayKeyCase($array, CASE_LOWER);
        try {
            $total = count($finalData);
            file_put_contents($file, 'Start importing ' . $total . ' stores...' . PHP_EOL, FILE_APPEND);

            for($i=0;$i<$total;$i++) {
                preg_match('#\(([^\)]+)\)#', $finalData[$i]['client_name'], $matches);
                $storeId=str_replace('#', '', $matches[1]);
                file_put_contents($file, 'Current: ' . $i . ' - ' . $storeId . date("Y-m-d H:i:s") . PHP_EOL, FILE_APPEND);

                //check if we have store id in database
                $checkStore = $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($storeId);

                if($checkStore) {
                    $checkYodle = $em->getRepository('AcmeDataBundle:Yodle')->findOneByStoreID($checkStore->getId());
                    if($checkYodle){
                        $yodle = $checkYodle;
                    }else{
                        $yodle = new Yodle();
                        $yodle->setAssetsUUID($finalData[$i]['essentials_widget_id']);
                        $yodle->setStoreID($checkStore->getId());
                        $yodle->setReviewsUUID($finalData[$i]['rateabiz_widget_id']);
                        $yodle->setYotrackUUID($finalData[$i]['client_id']);
                    }
                    $em->persist($yodle);
                    $em->flush();
                }

            }

            file_put_contents($file, $total . ' stores imported.' . PHP_EOL, FILE_APPEND);

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();
            //find keys
            $keys = $cache->find('*stores*');
            //delete cache
            if(!empty($keys)) {
                for($i=0;$i<count($keys);$i++) {
                    $cache->delete($keys[$i]);
                }
                file_put_contents($file, 'Redis Cache successfully deleted.' . PHP_EOL, FILE_APPEND);
            }

            //send email with log file
            //$this->container->get('emailNotificationBundle.email')->sendCronStoresLogs($file);

            return $this->notification = new Notification(true);

        } catch(\Exception $e) {
            file_put_contents($file, $e->getMessage() . PHP_EOL, FILE_APPEND);

            //send email with log file
            //$this->container->get('emailNotificationBundle.email')->sendCronStoresLogs($file);

            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}