<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 29.02.2016
 * Time: 14:31
 */

namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\EntitiesUtility;
use Acme\DataBundle\Entity\ServicesPrices as ServicesPr;

class ServicesPrices
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
            fclose($handle);
        }
        //get doctrine manager
        $em = $this->container->get('doctrine')->getManager();

        //convert all keys to lowercase
        $finalData = StringUtility::changeArrayKeyCase($array, CASE_LOWER);


        try {
            $total = count($finalData);
            for($i=0;$i<$total;$i++) {
                //stores are updated if they are not closed
                $checkStore = $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                if($checkStore)
                    $entity = $checkStore;


                $servicesPrices = $em->getRepository('AcmeDataBundle:ServicesPrices')->findOneBy(array("priceSet"=>$finalData[$i]['requestedby'],"standardPrice"=>$finalData[$i]['standardprice'],"highMileagePrice"=>$finalData[$i]['highmileageprice'],"fullSyntheticPrice"=>$finalData[$i]['fullsyntheticprice'] ));
                if(!$servicesPrices) {
                    $servicesPrices = new ServicesPr();
                    $servicesPrices->setStandardPrice($finalData[$i]['standardprice']);
                    $servicesPrices->setHighMileagePrice($finalData[$i]['highmileageprice']);
                    $servicesPrices->setFullSyntheticPrice($finalData[$i]['fullsyntheticprice']);
                    $servicesPrices->setPriceSet($finalData[$i]['requestedby']);
                    $em->persist($servicesPrices);
                    $em->flush();
                }
                $checkStoreService = $em->getRepository('AcmeDataBundle:StoresHasServices')->findBy(array('stores' => $entity));
                if($checkStoreService) {
                    foreach($checkStoreService as $sh){
                        if($sh->getServices()->getTitle()=='OilChangeService') {
                            $sh->setPrices($servicesPrices);
                            $em->persist($sh);
                            $em->flush();
                        }
                    }
                }
            }

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();
            //find keys
            $keys = $cache->find('*stores*');
            //delete cache
            if(!empty($keys)) {
                for($i=0;$i<count($keys);$i++) {
                    $cache->delete($keys[$i]);
                }

                return $this->notification = new Notification(true);
            }
        } catch (\Exception $e) {
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}