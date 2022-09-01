<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/7/18
 * Time: 3:31 PM
 */

namespace App\Service\Sender;


use App\Models\Contract;
use App\Models\Templates;

class PrivateSender
{
    static function generateAndSend(Contract $contract, $letter)
    {
        $template = Templates::where('alias', '=', $letter)->first();
        $template_html = $template->html_body;

        $today = new \DateTime();
        $month = $today->format('F');
        $year = $today->format('Y');
        $day = ltrim($today->format('d'), '0');
        if ($day % 10 == 1 && $day != 11) {
            $day .= 'st';
        } else if ($day % 10 == 2 && $day != 12) {
            $day .= 'nd';
        } else if ($day % 10 == 3 && $day != 13) {
            $day .= 'rd';
        } else {
            $day .= 'th';
        }
        $today = $day . ' ' . $month . ' ' . $year;


        $customer = $contract->customer()->first();
        $asset = $contract->assets()->first();
        $site = $asset->site()->first();

        $address = str_replace("\n", ', ', ucwords(strtolower($customer->address)));
        $address2 = '';
        $city = htmlentities(str_replace("\n", ', ', ucwords(strtolower($customer->city))));
        $state = htmlentities(str_replace("\n", ', ', ucwords(strtolower($customer->state))));
        $postcode = htmlentities(str_replace("\n", ', ', strtoupper($customer->postal_code)));

        $addressProperty = str_replace("\n", ', ', ucwords(strtolower($site->address)));
        $address2Property = '';
        $cityProperty = htmlentities(str_replace("\n", ', ', ucwords(strtolower($site->city))));
        $stateProperty = htmlentities(str_replace("\n", ', ', ucwords(strtolower($site->state))));
        $postcodeProperty = htmlentities(str_replace("\n", ', ', strtoupper($site->postal_code)));


        $contactName = htmlentities(str_replace("\n", ', ', ucwords(strtolower($customer->getName()))));

        $contractNo = $contract->contract_id;
        $customerId = $customer->company_id;
        $assetId = $asset->asset_id;
        $planType = $asset->value;
        $totalDue = $contract->value;
        $expiryDate = $contract->getExpireDate();

        $search = [
            '${Address}',
            '${Address2}',
            '${City}',
            '${County}',
            '${Postcode}',
            '${PropertyAddress}',
            '${PropertyAddress2}',
            '${PropertyCity}',
            '${PropertyCounty}',
            '${PropertyPostCode}',
            '${ContractNo}',
            '${CustomerID}',
            '${AssetID}',
            '${PlanType}',
            '${ContactName}',
            '${TodayDate}',
            '${TotalDue}',
            '${ExpiryDate}',
        ];

        $replace = [
            $address,
            $address2,
            $city,
            $state,
            $postcode,
            $addressProperty,
            $address2Property,
            $cityProperty,
            $stateProperty,
            $postcodeProperty,
            $contractNo,
            $customerId,
            $assetId,
            $planType,
            $contactName,
            $today,
            $totalDue,
            $expiryDate,
        ];

        $template_html = str_replace($search, $replace, $template_html);
        if ($customer->email) {
            Sender::send(
                $customer->email,
                $template->subject,
                $template_html
            );
        }
    }
}
