<?php

namespace Acme\DataBundle\Model\ClutchService;

use Symfony\Component\HttpFoundation\Request;

use Acme\DataBundle\Model\Utility\StringUtility;
use Acme\DataBundle\Entity\Users;
use Acme\DataBundle\Model\ClutchService\ClutchManager;
use Acme\DataBundle\Model\Utility\Curl;

class Clutch extends ClutchManager {

  public  function buildHeader($data) {
    $jsonData = json_encode($data);
    $headers = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData),
        'Authorization: Basic ' . base64_encode($this->api['api_key'] . ':' . $this->api['api_secret']),
        'Brand: ' . $this->api['brand'],
        'Location: ' . $this->api['location'],
        'Terminal: ' . $this->api['terminal']
    );
    return $headers;
  }

  public  function getCustomerData($data, $vehicle = 0) {
    $customerData = '';
    $header = self::buildHeader($data);
    $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'search', $this->api['api_port'], $data , 'POST',$header);
    if($curl['response']['success']) {
      $response = $curl['response'];
      if(!empty($response['cards'])) {
        if($vehicle) {
          $customerData['brandDemographics'] = isset($response['cards'][0]['brandDemographics']) ? json_decode($response['cards'][0]['brandDemographics'], true): array();
          $customerData['mailings'] = isset($response['cards'][0]['mailings']) ? $response['cards'][0]['mailings'] : array();
        } else {
          $customerData = $this->getCustomerCardInfo($response);
        }
      }
    }
    return $customerData;
  }

  public  function getCustomerDataForRegister($data) {
    $customerData = array();
    $header = self::buildHeader($data);
    $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'search', $this->api['api_port'], $data, 'POST',$header);
    if($curl['response']['success']) {
      $response = $curl['response'];
      if(!empty($response['cards'])) {
        //after mkey_ cards with keytag set
        if(empty($customerData)) {
          $customerData = $this->getCustomerCardInfo($response);
        }
      }
    }
    return $customerData;
  }

  public  function getCustomerCardHistory($data) {

    $header = self::buildHeader($data);
    $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'cardHistory', $this->api['api_port'], $data, 'POST',$header);

    //get results from Clutch
    $customerHistory = array();

    if($curl['response']['success']) {
      $response = $curl['response'];
      $customerHistory = $response['transactions'];
    }
    return $customerHistory;
  }

  public  function getCustomerCardTransaction($data) {

    $header = self::buildHeader($data);
    $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'checkoutLookup', $this->api['api_port'], $data, 'POST',$header);

    //get results from Clutch
    $customerTransaction = array();
    if($curl['response']['success']) {
      $response = $curl['response'];
      if(!empty($response['skus'])) {
        for($i=0;$i<count($response['skus']);$i++) {
          $customerTransaction[$i]['amount'] = isset($response['balanceMutations'][$i]['amount']) ? $response['balanceMutations'][$i]['amount'] : 0;
          $customerTransaction[$i]['sku'] = $response['skus'][$i]['sku'];
          $customerTransaction[$i]['locationId'] = isset($response['locationId']) ? $response['locationId'] : '';
        }
      }
    }
    return $customerTransaction;
  }

  public  function getCustomerLastLocation($data) {

    $header = self::buildHeader($data);
    $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'checkoutLookup', $this->api['api_port'], $data, 'POST',$header);

    //get results from Clutch
    $lastStore = '';

    if($curl['response']['success']) {
      $response = $curl['response'];
      $lastStore = isset($response['locationId']) ? $response['locationId'] : '';
    }
    return $lastStore;
  }

  public  function getCustomerInfo($email, $phoneNumber = "", $cardNumber = "") {

    //format phone number
    $phone = '+1' . StringUtility::formatPhoneNumber($phoneNumber, true);

    $returnFields = array(
        'balances' => true,
        'customer' => true,
        'alternateCustomer' => true,
        'giverCustomer' => true,
        'isEnrolled' => true,
        'customData' => true,
        'customCardNumber' => true,
        'brandDemographics' => true
    );

    if($cardNumber != "") {
      //first data array
      $data = array(
          'filters' => array(
              'cardNumber' => $cardNumber,
          ),
          'returnFields' => $returnFields
      );
      $customerData = self::getCustomerData($data);
      if(!empty($customerData))
        return $customerData;
    } else {
      //first data array
      $data = array(
          'filters' => array(
              'email' => $email,
              'phone' => $phone,
          ),
          'returnFields' => $returnFields
      );
      $customerData = self::getCustomerData($data);
      if(!empty($customerData))
        return $customerData;

      //second data array
      $data = array(
          'filters' => array(
              'email' => $email
          ),
          'returnFields' => $returnFields
      );
      $customerData = self::getCustomerData($data);
      if(!empty($customerData))
        return $customerData;

      //third data array
      $data = array(
          'filters' => array(
              'phone' => $phone
          ),
          'returnFields' => $returnFields
      );
      $customerData = self::getCustomerData($data);
    }
    return $customerData;
  }

  public  function getCustomerInfoForRegister($email = "", $customCardNumber = "", $phoneNumber = "") {

    $returnFields = array(
        'balances' => true,
        'customer' => true,
        'alternateCustomer' => true,
        'giverCustomer' => true,
        'isEnrolled' => true,
        'customData' => true,
        'customCardNumber' => true,
        'brandDemographics' => true
    );

    //search for custom card number
    if($customCardNumber) {
      $data = array(
          'filters' => array(
              'customCardNumber' => $customCardNumber
          ),
          'returnFields' => $returnFields
      );
    }
    //search for phone
    if($phoneNumber) {

      //format phone number
      $phone = '+1' . StringUtility::formatPhoneNumber($phoneNumber, true);

      $data = array(
          'filters' => array(
              'phone' => $phone
          ),
          'returnFields' => $returnFields
      );
    }
    //search for phone
    if($email) {

      $data = array(
          'filters' => array(
              'email' => $email
          ),
          'returnFields' => $returnFields
      );
      $customerData = self::getCustomerData($data);
    } else {
      $customerData = self::getCustomerDataForRegister($data);
    }
    return $customerData;
  }

  public function getHistoryTransaction($cardNumber, $period = '') {

    //data array
    if($period) {
      $data = array(
          'cardNumber' => $cardNumber,
          'beginDate' => date("Y-m-d H:i:s", strtotime('-' . $period . 'days'))
      );
    }
    else {
      $data = array(
          'cardNumber' => $cardNumber
      );
    }
    $customerData = self::getCustomerCardHistory($data);

    return $customerData;
  }

  public  function getTransactionDetails($transactionId) {
    //data array
    $data = array(
        'checkoutTransactionId' => $transactionId
    );
    $customerData = self::getCustomerCardTransaction($data);
    return $customerData;
  }

  public  function getTransactionDetailsForLastLocation($transactionId) {
    //data array
    $data = array(
        'checkoutTransactionId' => $transactionId
    );
    $customerData = self::getCustomerLastLocation($data);
    return $customerData;
  }

  public  function setCustomerInfo(Users $user) {
    $data = array(
        'cardNumber' => $user->getCardNumber(),
        'primaryCustomer' => array(
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'phone' => '+1' . StringUtility::formatPhoneNumber($user->getPhone(), true)
        )
    );
    $header = self::buildHeader($data);
    $response = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'updateAccount', $this->api['api_port'], $data, 'POST',$header);

    if($response['response']['success']) {
      return true;
    }
    return false;
  }

  public  function allocateCard() {
    $data = array(
        'cardSetId' => 'MKEY2015'
    );
    $header = self::buildHeader($data);
    $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'allocate', $this->api['api_port'], $data, 'POST',$header);
    if($curl['response']['success']) {
      $response = $curl['response'];
      return $response['cardNumber'];
    }
    return false;
  }

  public  function allocateCustomerInfo($userData) {

    $data = array(
        'cardNumber' => $userData['cardNumber'],
        'countAsEnrollment'=> $userData['countAsEnrollment'],
        'primaryCustomer' => array(
            'firstName' => $userData['firstName']?$userData['firstName']:'',
            'lastName' => $userData['lastName']?$userData['lastName']:'',
            'phone' => $userData['phone']? '+1' . StringUtility::formatPhoneNumber($userData['phone'], true):'',
            'email' => $userData['email']
        )
    );
    $header = self::buildHeader($data);
    $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'updateAccount', $this->api['api_port'], $data, 'POST',$header);
    if($curl['response']['success']) {
      $response = $curl['response'];
      if($response['requestRef']) {
        return $response['requestRef'];
      }
    }
    return null;
  }

  public  function getVehicleInfo($cardNumber) {
    $returnFields = array(
        'balances' => true,
        'customer' => true,
        'alternateCustomer' => true,
        'giverCustomer' => true,
        'isEnrolled' => true,
        'customData' => true,
        'customCardNumber' => true,
        'brandDemographics' => true,
        'mailings' => true
    );
    $data = array(
        'filters' => array(
            'cardNumber' => $cardNumber
        ),
        'returnFields' => $returnFields
    );
    $customerData = self::getCustomerData($data, 1);
    return $customerData;
  }

  /**
   * @param $api
   * @param $response
   * @return mixed
   */
  public  function getCustomerCardInfo($response) {
    $recentCard = array();
    //card types
    for($i=0;$i<count($response['cards']);$i++) {
      if(preg_match('/mkey_/', $response['cards'][$i]['cardNumber']) && isset($response['cards'][$i]['customCardNumber']) && $response['cards'][$i]['customCardNumber']) {
        //get all transactions for the mkey cards
        $recentCard['mkey1'][$response['cards'][$i]['cardNumber']] = $this->getCardRecentTransaction($response['cards'][$i]['cardNumber']);
      } elseif (preg_match('/cust_/', $response['cards'][$i]['cardNumber'])  && isset($response['cards'][$i]['customCardNumber']) && $response['cards'][$i]['customCardNumber']) {
        //get all transactions for the cust cards
        $recentCard['cust1'][$response['cards'][$i]['cardNumber']] = $this->getCardRecentTransaction($response['cards'][$i]['cardNumber']);
      } elseif (preg_match('/mkey_/', $response['cards'][$i]['cardNumber'])) {
        //get all transactions for all the mkey cards without customCardNumber
        $recentCard['mkey2'][$response['cards'][$i]['cardNumber']] = $this->getCardRecentTransaction($response['cards'][$i]['cardNumber']);
      } elseif (preg_match('/cust_/', $response['cards'][$i]['cardNumber'])) {
        //get all transactions for all the cust cards without customCardNumber
        $recentCard['cust2'][$response['cards'][$i]['cardNumber']] = $this->getCardRecentTransaction($response['cards'][$i]['cardNumber']);
      }
    }

    //set the priority for cards
    switch($recentCard) {
      case array_key_exists('mkey1',$recentCard) :
        $customerData = $this->getCardInfo('mkey1',$recentCard,$response);
        break;
      case array_key_exists('cust1',$recentCard) :
        $customerData = $this->getCardInfo('cust1',$recentCard,$response);
        break;
      case array_key_exists('mkey2',$recentCard) :
        $customerData = $this->getCardInfo('mkey2',$recentCard,$response);
        break;
      case array_key_exists('cust2',$recentCard) :
        $customerData = $this->getCardInfo('cust2',$recentCard,$response);
        break;
      default :
        $customerData=array();
    }
    return $customerData;
  }

  /**
   * Get the most recent transaction for the card number
   *
   * @param $api
   * @param $cardNumber
   * @return mixed
   */
  public function getCardRecentTransaction($cardNumber) {
    $transactionsTime = '';
    $transactions = $this->getHistoryTransaction($cardNumber, '');
    for($i=0; $i<count($transactions); $i++) {
      $transactionsTime[$i]=$transactions[$i]['transactionTime'];
    }
    return max($transactionsTime);
  }

  /**
   * Select card type by most recent history transactions
   * Return card data
   *
   * @param $type
   * @param $recentCard
   * @param $response
   * @return mixed
   */
  public  function getCardInfo($type, $recentCard, $response) {
    $cardNumber = array();
    $transaction =array();
    foreach($recentCard[$type] as $cardNumber => $recentTransaction ) {
      $transaction[$cardNumber] = $recentTransaction;
    }
    //get the most recent card type  used in history transactions
    $cardNumber = array_search(max($transaction), $transaction);

    for($i=0;$i<count($response['cards']);$i++) {
      if($cardNumber == $response['cards'][$i]['cardNumber']) {
        $customerData['cardNumber'] = $response['cards'][$i]['cardNumber'];
        $customerData['customCardNumber'] = isset($response['cards'][$i]['customCardNumber']) ? $response['cards'][$i]['customCardNumber'] : '';
        $customerData['balance'] = !empty($response['cards'][$i]['balances']) ? (isset($response['cards'][$i]['balances'][0]['amount']) ? $response['cards'][$i]['balances'][0]['amount'] : 0) : 0;
        $customerData['firstName'] = isset($response['cards'][$i]['customer']['firstName']) ? $response['cards'][$i]['customer']['firstName'] : '';
        $customerData['lastName'] = isset($response['cards'][$i]['customer']['lastName']) ? $response['cards'][$i]['customer']['lastName'] : '';
        $customerData['email'] = isset($response['cards'][$i]['customer']['email']) ? $response['cards'][$i]['customer']['email'] : '';
        $customerData['phone'] = isset($response['cards'][$i]['customer']['phone']) ? str_replace("+1", "", $response['cards'][$i]['customer']['phone']) : '';
        $customerData['brandDemographics'] = isset($response['cards'][$i]['brandDemographics']) ? json_decode($response['cards'][$i]['brandDemographics'], true): array();
      }
    }
    return $customerData;
  }

  public function setVehicleDetails($vehicleDetails) {
    $data['cardNumber']=$vehicleDetails['cardNumber'];
    if($vehicleDetails['image']!='')
      $data['brandDemographics'][] = array('fieldName'=>'image', 'value'=>$vehicleDetails['image']);
    if($vehicleDetails['vehicleNickname']!='')
      $data['brandDemographics'][] = array('fieldName'=>'vehicleNickname', 'value'=>$vehicleDetails['vehicleNickname']);
    if($vehicleDetails['shortNote']!='')
      $data['brandDemographics'][] = array('fieldName'=>'shortNote', 'value'=>$vehicleDetails['shortNote']);

    $header = self::buildHeader($data);
    $response = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'updateAccount', $this->api['api_port'], $data, 'POST',$header);

    return $response;
  }

  public function updateBalance($userData) {

    $data = array(
        'cardNumber' => $userData['cardNumber'],
        'action'=> $userData['action'],
        'amount' => array(
            'balanceType' => $userData['amount']['balanceType'],
            'amount' => $userData['amount']['amount'],
        ),
        "issuedBalanceExpiration"=>$userData['issuedBalanceExpiration']
    );

    $header = self::buildHeader($data, $userData['clutch']);
    $response = Curl::curl($userData['clutch']['api_url'] . $userData['clutch']['api_service'] . 'updateBalance', $userData['clutch']['api_port'], $data, 'POST',$header);

    if($response['response']['success']) {
      return true;
    }
    return false;
  }
  
  public function getCardByCardNumber($cardNumber){
	  $data = array(
	  	'filters' => array(
			  'cardNumber' => $cardNumber
		  ),
		  'returnFields' => array(
			  "balances" => true,
			  "customer" => true,
			  "customCardNumber" => true,
			  "brandDemographics" => true
		  )
	  );
	  $header = self::buildHeader($data);
	  $curl = Curl::curl($this->api['api_url'] . $this->api['api_service'] . 'search', $this->api['api_port'], $data , 'POST',$header);
	  
	  if($curl['status'] == 200 && !empty($curl['response']['cards'])){
	  	return true;
	  }
	  return false;
  }
}