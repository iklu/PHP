<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 18.10.2016
 * Time: 12:13
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions;


class FiltersToApply
{
    const CHECKOUT_COMPLETE = "CHECKOUT_COMPLETE";
const TRANSACTION_CARD_HISTORY = "TRANSACTION_CARD_HISTORY";
    const SKU_TYPE_1  = "SKU_TYPE_1";
    const SKU_TYPE_2  = "SKU_TYPE_2";
    const SKU_CODE_1  = "SKU_CODE_1";
    const SKU_CODE_2  = "SKU_CODE_2";
    const SERVICE_REMINDERS  = "SERVICE_REMINDERS";
    const TRANSACTION_LOCATION  = "TRANSACTION_LOCATION";
    const TRANSACTION_LAST_LOCATION  = "TRANSACTION_LAST_LOCATION";
}