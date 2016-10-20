<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 11.10.2016
 * Time: 12:17
 */

namespace Acme\DataBundle\Model\ClutchService\Account;


class CardNumber implements ClutchInterface
{

    /**
     * @var
     */
    public $cardNumber;

    /**
     * @var
     */
    public $customCardNumber;

    /**
     * @var
     */
    public $balance;

    /**
     * @var
     */
    public $firstName;

    /**
     * @var
     */
    public $lastName;

    /**
     * @var
     */
    public $email;

    /**
     * @var
     */
    public $phone;


    /**
     * @var
     */
    public $brandDemographics;

    /**
     * @var
     */
    public $mailings;

    /**
     * @var
     */
    public $usedCache;

    /**
     * @var
     */
    public $success;

    /**
     * @param $cardNumber
     * @return $this
     */
    public function setCardNumber($cardNumber){
        $this->cardNumber = $cardNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCardNumber(){
        return $this->cardNumber;
    }

    /**
     * @param $customCardNumber
     * @return $this
     */
    public function setCustomCardNumber($customCardNumber){
        $this->customCardNumber = $customCardNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomCardNumber() {
        return $this->customCardNumber;
    }


    /**
     * @param $balance
     * @return $this
     */
    public function setBalance($balance) {
        $this->balance = $balance;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBalance(){
        return $this->balance;
    }

    /**
     * @param $firstName
     * @return $this
     */
    public function setFirstName($firstName){
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * @param $lastName
     * @return $this
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param $phone
     * @return $this
     */
    public function setPhone($phone) {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone() {
        return $this->phone;
    }

    /**
     * @param $brandDemographics
     * @return mixed
     */
    public function setBrandDemographics($brandDemographics) {
        $this->brandDemographics = $brandDemographics;
        return $this->brandDemographics;
    }

    /**
     * @return mixed
     */
    public function getBrandDemographics() {
       return $this->brandDemographics;
    }

    /**
     * @param $mailings
     * @return $this
     */
    public function setMailings($mailings) {
        $this->mailings = $mailings;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMailings() {
        return $this->mailings;
    }
    
}