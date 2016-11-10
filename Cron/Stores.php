<?php
namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
use Acme\DataBundle\Model\Utility\FullSlate;
use Acme\DataBundle\Entity\Stores as NewStore;
use Acme\DataBundle\Model\Utility\Logs;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 27.01.2016
 * Time: 15:04
 */
class Stores extends Cron implements CronInterface
{
    public function add($csvFile, $logFile, $params=array()) {

        $finalData = $this->getCsvImportData($csvFile, $logFile);

        try {
            $total = count($finalData);
            $openStores = 0;
            $newOpenStores = 0;
            $newClosedStores = 0;

            Logs::write($this->logFile , 'Start importing ' . $total . ' stores...');

            for($i=0;$i<$total;$i++) {

                $csvClosed      = preg_match( "#".StoresStatus::CLOSED."#i", $finalData[$i]['statusflag']);
                $csvOpen        = preg_match( "#".StoresStatus::OPEN."#i", $finalData[$i]['statusflag']);
                $csvPipeline    = preg_match( "#".StoresStatus::PIPELINE."#i", $finalData[$i]['statusflag']);

                //stores are updated if they are not closed
                if(!$csvClosed) {

                    Logs::write($this->logFile , 'Current: ' . $i . ' - ' . $finalData[$i]['shopnumber'] . ' - '.$finalData[$i]['statusflag'].' - ' . date("Y-m-d H:i:s"));

                    //check if we have store id in database
                    $checkStore = $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                    $newStore = 0;
                    if($checkStore) {
                        $entity = $checkStore;

                        $locationStatus = $checkStore->getLocationStatus();

                        if($csvOpen)
                            $openStores++;

                        if(preg_match( "#".StoresStatus::PIPELINE."#i", $locationStatus) && $csvOpen) {
                            $newOpenStores++;
                            //send email for subscribers
                            $subscribers = $this->em->getRepository('AcmeDataBundle:PipelineSubscribers')->findByStores($checkStore);
                            if($subscribers) {
                                for($j=0;$j<count($subscribers);$j++) {
                                    $this->get('emailNotificationBundle.email')->sendPipeline($subscribers[$j]->getEmail(), $checkStore);
                                }
                            }
                        }
                    } else {
                        $entity = new NewStore();
                        $newStore = 1;
                        $timezone = NULL;
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
                    if($csvOpen){
                        $entity->setLocationStatus('OPEN');
                    } elseif($csvPipeline){
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
                    }
                    $entity->setHasFullSlate(0);

                    $this->em->persist($entity);
                    $this->em->flush();
                } else {
                    //check if we have store id in database
                    $entity = $this->em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                    if($entity) {

                        if(preg_match( "#".StoresStatus::OPEN."#i", $entity->getLocationStatus())) {

                            $newClosedStores++;

                            //send email to know that the franchise has closed
                            $this->container->get('emailNotificationBundle.email')->sendClosed($entity);
                        }

                        //set status closed
                        $entity->setLocationStatus(strtoupper($finalData[$i]['statusflag']));
                        $this->em->flush();
                    }
                }
            }

            Logs::write($this->logFile , $total . ' stores imported.');
            Logs::write($this->logFile , $openStores . ' open stores.');
            Logs::write($this->logFile , $newOpenStores . ' newly open stores.');
            Logs::write($this->logFile , $newClosedStores . ' newly closed stores.');

            //delete redis cache
            $cache = $this->container->get('cacheManagementBundle.redis')->initiateCache();
            //find keys
            $keys = $cache->find('*stores*');
            //delete cache
            if(!empty($keys)) {
                for($i=0;$i<count($keys);$i++) {
                    $cache->delete($keys[$i]);
                }
                Logs::write($this->logFile , 'Stores Cache successfully deleted.');
            }
            return $this->notification = new Notification(true);

        } catch(\Exception $e) {

            Logs::write($this->logFile , $e->getMessage());
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}