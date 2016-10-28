<?php
namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\EntitiesUtility;
use Acme\DataBundle\Entity\StoresHasServices;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 27.01.2016
 * Time: 15:04
 */
class StoresServices
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
                if(strtoupper($finalData[$i]['statusflag']) !== StoresStatus::CLOSED) {
                    $checkStore = $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                    if($checkStore)
                        $entity = $checkStore;

                    //add store services
                    $services = EntitiesUtility::getCSVServices();
                    for($j=0;$j<count($services);$j++) {
                        $checkService = $em->getRepository('AcmeDataBundle:Services')->findOneByTitle($services[$j]);

                        if($checkService) {

                            $checkStoreService = $em->getRepository('AcmeDataBundle:StoresHasServices')->findOneBy(array('stores' => $entity, 'services' => $checkService));

                            if($finalData[$i][$services[$j]]) {
                                if(!$checkStoreService) {
                                    //add to DB
                                    $entitySHS = new StoresHasServices();
                                    $entitySHS->setStores($entity);
                                    $entitySHS->setServices($checkService);

                                    $em->persist($entitySHS);
                                    $em->flush();
                                }
                            }
                            else {
                                if($checkStoreService) {
                                    //remove from DB
                                    $em->remove($checkStoreService);
                                    $em->flush();
                                }
                            }
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