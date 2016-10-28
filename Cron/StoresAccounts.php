<?php
namespace Acme\DataBundle\Model\Cron;
use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Model\Utility\Notification;
use Acme\DataBundle\Model\Constants\StoresStatus;
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 27.01.2016
 * Time: 15:03
 */
class StoresAccounts
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
        $accountsFile = $this->container->getParameter('project')['site_path'] . $this->container->getParameter('project')['upload_dir_documents'] . 'cron-add-accounts' . date("Y-m-d") . '.txt';
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
                file_put_contents($accountsFile, 'Error: unexpected fgets() fail.' . PHP_EOL, FILE_APPEND);
                exit();
            }
            fclose($handle);
        }
        //}

        //get doctrine manager
        $em = $this->container->get('doctrine')->getManager();

        //convert all keys to lowercase
        $finalData = StringUtility::changeArrayKeyCase($array, CASE_LOWER);

        try {
            $total = count($finalData);
            $newUserAccount = 0;
            file_put_contents($accountsFile, 'Start adding users' . PHP_EOL, FILE_APPEND);

            for($i=0;$i<$total;$i++) {
                //stores are updated if they are not closed
                if(strtoupper($finalData[$i]['statusflag']) !== StoresStatus::CLOSED) {
                    $url=$this->container->getParameter('project')['api_accounts_import_url']."app/v1/accounts/import";
                    $shop_number = $finalData[$i]['shopnumber'];
                    $email = $shop_number . '@seo-xivic.com';
                    $role = 'FRANCHISEE';
                    $userType = 'client';
                    $firstName = $finalData[$i]['locationcity'];
                    $lastName = $finalData[$i]['streetaddress1'] . ', ' . $finalData[$i]['locationcity'] . ', ' . $finalData[$i]['locationstate'] . ', ' . $finalData[$i]['locationpostalcode'];
                    $storeNumber = $shop_number;
                    $password = $shop_number . '_develop13#';
                    $data = array('email' => $email, 'role' => $role, 'userType' => $userType, 'firstName' => $firstName, 'lastName' => $lastName, 'storeNumber' => $storeNumber, 'password' => $password, 'passwordConfirmation' => $password);
                    try {
                        $options = array(
                            'http' => array(
                                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method' => 'POST',
                                'content' => http_build_query($data),
                            ),
                        );
                        $context = stream_context_create($options);
                        $result = file_get_contents($url, false, $context);
                        $response = json_decode($result);

                        if($response->success) {
                            file_put_contents($accountsFile, 'Current: ' . $i . ' - Inserted ID: '.$response->entity.' - '. $email. ' - ' . date("Y-m-d H:i:s") . PHP_EOL, FILE_APPEND);
                            $newUserAccount++;
                        }
                    } catch(Exception $ex){
                        file_put_contents($accountsFile, $ex->getMessage() . PHP_EOL, FILE_APPEND);
                    }
                }
            }
            file_put_contents($accountsFile, $newUserAccount . ' users created.' . PHP_EOL, FILE_APPEND);
            //send email with log file
            $this->container->get('emailNotificationBundle.email')->sendCronStoresLogs($accountsFile);
            return $this->notification = new Notification(true);

        } catch (\Exception $e) {
            file_put_contents($accountsFile, $e->getMessage() . PHP_EOL, FILE_APPEND);

            //send email with log file
           $this->container->get('emailNotificationBundle.email')->sendCronStoresLogs($accountsFile);

            return $this->notification = new Notification(false , $e->getMessage());
        }
    }
}