<?php
namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\FullSlate;
use Acme\DataBundle\Entity\Stores as NewStore;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 27.01.2016
 * Time: 15:04
 */
class Stores
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
            $openStores = 0;
            $newOpenStores = 0;
            $newClosedStores = 0;
            $newUserAccount = 0;
            file_put_contents($file, 'Start importing ' . $total . ' stores...' . PHP_EOL, FILE_APPEND);

            for($i=0;$i<$total;$i++) {
                //stores are updated if they are not closed
                if(strtoupper($finalData[$i]['statusflag']) !== StoresStatus::CLOSED) {
                    file_put_contents($file, 'Current: ' . $i . ' - ' . $finalData[$i]['shopnumber'] . ' - '.$finalData[$i]['statusflag'].' - ' . date("Y-m-d H:i:s") . PHP_EOL, FILE_APPEND);

                    //check if we have store id in database
                    $checkStore = $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                    $newStore = 0;
                    $hasFullSlate = 1;
                    if($checkStore) {
                        $entity = $checkStore;

                        $locationStatus = $checkStore->getLocationStatus();

                        if(strtoupper($finalData[$i]['statusflag']) == StoresStatus::OPEN)
                            $openStores++;

                        if($locationStatus == StoresStatus::PIPELINE && strtoupper($finalData[$i]['statusflag']) == StoresStatus::OPEN) {
                            $newOpenStores++;
                            //send email for subscribers
                            $subscribers = $em->getRepository('AcmeDataBundle:PipelineSubscribers')->findByStores($checkStore);
                            if($subscribers) {
                                for($j=0;$j<count($subscribers);$j++) {
                                    $this->get('emailNotificationBundle.email')->sendPipeline($subscribers[$j]->getEmail(), $checkStore);
                                }
                            }
                        }
                    } else {
                        $entity = new NewStore();
                        $newStore = 1;
                        //Full Slate parameters
                        //$hasFullSlate = 1;
                        $timezone = NULL;
                    }

                    //check full slate for all open stores
                    if(strtoupper($finalData[$i]['statusflag']) == StoresStatus::OPEN) {
                        $checkFullSlate = FullSlate::checkFullSlate($finalData[$i]['shopnumber'], $this->container->getParameter('fullslate')['fullslate_url']);

                        if(strpos($checkFullSlate, 'There is no scheduling page') !== FALSE || strpos($checkFullSlate, 'This Full Slate site is no longer active') !== FALSE)
                            $hasFullSlate = 0;
                        else
                            $hasFullSlate = 1;
                    }

                    //add or update data
                    $entity->setStoreId($finalData[$i]['shopnumber']);
                    $entity->setStreetAddress1($finalData[$i]['streetaddress1']);
                    $entity->setStreetAddress2($finalData[$i]['streetaddress2']);
                    $entity->setLocationCity(ucwords(strtolower($finalData[$i]['locationcity'])));
                    $entity->setLocationState($finalData[$i]['locationstate']);
                    $entity->setLocationPostalCode($finalData[$i]['locationpostalcode']);
                    $entity->setLocationRegion($finalData[$i]['locationregion']);
                    $entity->setLocationEmail($finalData[$i]['locationemail']);
                    if(preg_match('#OPEN#', strtoupper($finalData[$i]['statusflag']))){
                        $entity->setLocationStatus('OPEN');
                    } elseif(preg_match('#PIPELINE#', strtoupper($finalData[$i]['statusflag']))){
                        $entity->setLocationStatus('PIPELINE');
                    }
                    $entity->setPhone($finalData[$i]['phone'] ? StringUtility::formatPhoneNumber($finalData[$i]['phone']) : NULL);
                    $entity->setRawPhone($finalData[$i]['phone'] ? StringUtility::formatPhoneNumber($finalData[$i]['phone'], true) : NULL);
                    $entity->setLng($finalData[$i]['longitude']);
                    $entity->setLat($finalData[$i]['latitude']);
                    $entity->setFacebookURL($finalData[$i]['facebook']);
                    $entity->setGoogleplusURL($finalData[$i]['google +']);
                    $entity->setYelpURL($finalData[$i]['yelp']);
                    $entity->setYellowPagesURL($finalData[$i]['yp.com']);
                    $entity->setStoreURL($finalData[$i]['url']);
                    $entity->setPrimaryContact(ucwords(strtolower($finalData[$i]['centerprimarycontact'])));
                    $entity->setHoursWeekdayOpen($finalData[$i]['hoursweekdayopen']);
                    $entity->setHoursWeekdayClose($finalData[$i]['hoursweekdayclose']);
                    $entity->setHoursSaturdayOpen($finalData[$i]['hourssaturdayopen']);
                    $entity->setHoursSaturdayClose($finalData[$i]['hourssaturdayclose']);
                    $entity->setHoursSundayOpen($finalData[$i]['hourssundayopen']);
                    $entity->setHoursSundayClose($finalData[$i]['hourssundayclose']);
                    $entity->setLocationDirections($finalData[$i]['locationdirections']);
                    $entity->setStarRating($finalData[$i]['starrating'] ? $finalData[$i]['starrating'] : NULL);
                    $entity->setOpenDate($finalData[$i]['opendate'] ? new \DateTime(date("Y-m-d", strtotime($finalData[$i]['opendate']))) : NULL);
                    $entity->setAmericanExpress($finalData[$i]['american express']);
                    $entity->setVisa($finalData[$i]['visa']);
                    $entity->setDiscover($finalData[$i]['discover']);
                    $entity->setMastercard($finalData[$i]['mastercard']);
                    $entity->setCareCareOne($finalData[$i]['care care one']);
                    $entity->setSeniorDiscount($finalData[$i]['senior citizen discount']);
                    $entity->setAaaDiscount($finalData[$i]['aaa discount']);
                    $entity->setCommunityServiceDiscount($finalData[$i]['community service discount']);
                    $entity->setCustomerLoyaltyDiscount($finalData[$i]['customer loyalty discount']);
                    $entity->setType($finalData[$i]['centertype']);
                    $entity->setIsFleet($finalData[$i]['fleet certified']);
                    $entity->setMaacoCertified($finalData[$i]['maaco certified']);
                    $entity->setICarGold($finalData[$i]['i-car gold']);
                    if($newStore) {
                        $entity->setTimezone($timezone);
                        //$entity->setHasFullSlate($hasFullSlate);
                    }
                    $entity->setHasFullSlate($hasFullSlate);

                    $em->persist($entity);
                    $em->flush();
                } else {
                    //check if we have store id in database
                    $entity = $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                    if($entity) {
                        if($entity->getLocationStatus() == StoresStatus::OPEN) {

                            $newClosedStores++;

                            //send email to know that the franchise has closed
                            $this->container->get('emailNotificationBundle.email')->sendClosed($entity);
                        }

                        //set status closed
                        $entity->setLocationStatus(strtoupper($finalData[$i]['statusflag']));
                        $em->flush();
                    }
                }
            }

            file_put_contents($file, $total . ' stores imported.' . PHP_EOL, FILE_APPEND);
            file_put_contents($file, $openStores . ' open stores.' . PHP_EOL, FILE_APPEND);
            file_put_contents($file, $newOpenStores . ' newly open stores.' . PHP_EOL, FILE_APPEND);
            file_put_contents($file, $newClosedStores . ' newly closed stores.' . PHP_EOL, FILE_APPEND);

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