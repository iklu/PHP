<?php
/**
 * Created by PhpStorm.
 * User: ovidiu
 * Date: 13.10.2016
 * Time: 18:18
 */

namespace Acme\DataBundle\Model\ClutchService\Transactions\Filters;
use Acme\DataBundle\Model\ClutchService\Transactions\Library\FilterInterface;
use Acme\DataBundle\Model\ClutchService\Transactions\FiltersToApply;
use Acme\DataBundle\Model\ClutchService\Transactions\Filter;


class TransactionServiceRemindersCriteria extends Filter  implements FilterInterface
{
    public function execute($transaction) {

        $data = $this->handleData($transaction);

        if (FiltersToApply::SERVICE_REMINDERS) {

            for($v = 0; $v < count($data["vehicles"]); $v++){

                $vehicle = $data["vehicles"][$v]['vehicle'];

                for($i = 0; $i < count($data["vehicles"][$v]["mailings"]); $i++ ){

                    if($data["vehicles"][$v]["mailings"][$i]["mailingListId"] == "Meineke06") {
                        $data["serviceReminders"][$vehicle][] = 'Oil Change Service Reminder';
                    }

                    if($data["vehicles"][$v]["mailings"][$i]["mailingListId"] == "Meineke07"){
                        $data["serviceReminders"][$vehicle][] = 'Oil Change & Brake Service Reminder';
                    }

                    if($data["vehicles"][$v]["mailings"][$i]["mailingListId"] == "Meineke17") {
                        $data["serviceReminders"][$vehicle][] = 'High Mileage Service Reminder';
                    }

                    if($data["vehicles"][$v]["mailings"][$i]["mailingListId"] == "Meineke19") {
                        $data["serviceReminders"][$vehicle][] = 'Oil Change Reminder';
                    }
                }
            }
        }

        $this->setUpdated($data);
        return $this->getUpdated();
    }
}