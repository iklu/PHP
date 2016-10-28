<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 17.02.2016
 * Time: 10:37
 */

namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;

class StoresTrackingPhone
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
        $file = $this->container->getParameter('project')['site_path'] . $this->container->getParameter('project')['upload_dir_documents'] . 'tracking-phone-cron' . date("Y-m-d") . '.txt';
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
            $newPhone = 0;

            file_put_contents($file, 'Start importing   ' . $total . ' tracking phones... at .'.$startTime.' '. PHP_EOL, FILE_APPEND);
            for($i=0; $i<$total; $i++ ) {
                $store =  $em->getRepository('AcmeDataBundle:Stores')->findOneByStoreId($finalData[$i]['shopnumber']);
                if($store) {
                    $newPhone++;
                    $store->setTrackingPhone($finalData[$i]['tracking number'] ? StringUtility::formatPhoneNumber($finalData[$i]['tracking number']) : NULL);
                    $store->setRawTrackingPhone($finalData[$i]['tracking number'] ? StringUtility::formatPhoneNumber($finalData[$i]['tracking number'], true) : NULL);
                    $em->persist($store);
                    $em->flush();
                }
            }

            $endTime = date('H:i:s', time());
            file_put_contents($file, 'End import at'. $endTime . ' ' . PHP_EOL, FILE_APPEND);
            file_put_contents($file, $newPhone . ' newly organic call tracking numbers ' . PHP_EOL, FILE_APPEND);

            return $this->notification = new Notification(true);

        } catch(\Exception $e) {
            file_put_contents($file, $e->getMessage() . PHP_EOL, FILE_APPEND);
            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}