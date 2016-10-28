<?php
namespace Acme\DataBundle\Model\Cron;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.02.2016
 * Time: 11:58
 */

use Acme\DataBundle\Entity\Dma;
use Acme\DataBundle\Entity\StoresHasDma;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;

class StoresDMA
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
        $file = $this->container->getParameter('project')['site_path'] . $this->container->getParameter('project')['upload_dir_documents'] . 'dma-cron' . date("Y-m-d") . '.txt';
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
            $startTime = date('H:i:s', time());
            $total = count($finalData);
            $newDMA = 0;
            $newStoresHasDma = 0;
            file_put_contents($file, 'Start importing   ' . $total . ' DMA... at .'.$startTime.' '. PHP_EOL, FILE_APPEND);
            for($i=0; $i<$total; $i++ ) {
                $dma = $em->getRepository('AcmeDataBundle:Dma')->findOneBy(array("city" => $finalData[$i]['locationcity'], "state" => $finalData[$i]['locationstate']));
                $store =  $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                if(strtoupper($finalData[$i]['statusflag']) === StoresStatus::OPEN) {
                    if(!$dma ) {
                        $dma = new Dma();
                        $dma->setCity($finalData[$i]['locationcity']);
                        $dma->setState($finalData[$i]['locationstate']);
                        $dma->setDmaName($finalData[$i]['dmaname']);
                        $dma->setDmaId($finalData[$i]['dmanumber']);
                        $em->persist($dma);
                        $em->flush();
                        $newDMA++;
                    }
                    $storesHasDma = $em->getRepository('AcmeDataBundle:StoresHasDma')->findOneBy(array('stores'=>$store->getId(), 'dma'=>$dma->getId()));
                    if(!$storesHasDma) {
                        $storesHasDma = new StoresHasDma();
                        $storesHasDma->setCity($dma);
                        $storesHasDma->setStores($store);
                        $em->persist($storesHasDma);
                        $em->flush();
                        $newStoresHasDma++;
                    }

                } else {
                    if($dma && $store) {
                        $storesHasDma = $em->getRepository('AcmeDataBundle:StoresHasDma')->findOneBy(array('stores'=>$store->getId(), 'dma'=>$dma->getId()));
                        if($storesHasDma) {
                            $em->remove($storesHasDma);
                            $em->flush();
                        }
                    }
                }
            }

            $endTime = date('H:i:s', time());
            file_put_contents($file, 'End import at'. $endTime . ' ' . PHP_EOL, FILE_APPEND);
            file_put_contents($file, $newDMA . ' newly dma' . PHP_EOL, FILE_APPEND);
            file_put_contents($file, $newStoresHasDma . ' newly stores added to DMA' . PHP_EOL, FILE_APPEND);

            return $this->notification = new Notification(true);

        } catch(\Exception $e) {
            file_put_contents($file, $e->getMessage() . PHP_EOL, FILE_APPEND);
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}