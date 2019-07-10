<?php

namespace App\Service;


use App\Helpers\Date;
use App\Models\PrivateCostCenter;
use App\Models\PrivateCustomer;
use App\Templates;
use Illuminate\Support\Facades\Config;
use PhpOffice\PhpWord\TemplateProcessor;

class PrivateTemplateGenerator
{
    public function generateDocx(PrivateCustomer $customer)
    {
        $template = $customer->type == PrivateCustomer::ANNUAL
            ? Templates::PRIVATE_ANNUAL
            : Templates::PRIVATE_DEBIT;

        $template = Templates::where('alias', $template)->first();
        return $this->fillPrivateDocxTemplate($template->docx, $customer);
    }

    public function fillPrivateDocxTemplate($docx, PrivateCustomer $customer)
    {
        $docxFolder = Config::get('constants.storage_docx');
        $file = $docxFolder . '/' . $docx;
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($file);

        if ($customer->type == PrivateCustomer::DEBIT) {
            $isDebit = true;
            $customers = [$customer];
        } else {
            $isDebit = false;
            $customers = PrivateCustomer::where('customer_id', $customer->customer_id)
                ->where('next_recurring_date', $customer->next_recurring_date)
                ->get();
        }

        $countCustomers = count($customers);
        if ($countCustomers > 0) {
            $templateProcessor->cloneBlock('CLONE', $countCustomers);
        }

        $recurringDate = $customer->next_recurring_date;
        $recurringDate = \DateTime::createFromFormat('Y-m-d', $recurringDate);
        $recurringDate = Date::prettyFormatting($recurringDate);

        $this
            ->fillHeadingCustomerInfo(
                $templateProcessor,
                $customer->getName(),
                $customer->customer_address,
                $customer->customer_city,
                $customer->customer_state,
                $customer->customer_postal_code
            )
            ->fillDefaultValues(
                $templateProcessor,
                $customer->customer_id,
                $recurringDate
            )
            ->fillCostCenterTables(
                $templateProcessor,
                $customers
            )
            ->fillDebitInfo(
                $templateProcessor,
                $customer
            );

        $today = new \DateTime();
        $name = $isDebit ? 'Debit' : 'Annual';
        $newFile = $customer->customer_id . '.' . $name . '.' . $today->format('Y-m-d') . '.docx';

        if ($customer->docx) {
            $oldFile = $docxFolder . '/' . $customer->docx;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        $source = $docxFolder . '/' . $newFile;
        $templateProcessor->saveAs($source);
        return $newFile;
    }

    public function fillDebitInfo(TemplateProcessor $templateProcessor, $customer)
    {
        $templateProcessor->setValue('PayersName', TemplateGenerator::getCorrectString($customer->payer_account_name));
        $templateProcessor->setValue('PayersReference', TemplateGenerator::getCorrectString($customer->payer_reference));
        $templateProcessor->setValue('DDPaymentPeriod', TemplateGenerator::getCorrectString($customer->period));
        $templateProcessor->setValue('DDPaymentDate', TemplateGenerator::getCorrectString($customer->getDirectDate()));
    }


    public function fillCostCenterTables(TemplateProcessor $templateProcessor, $customers)
    {
        /** @var PrivateCustomer $customer */
        foreach ($customers as $customer) {


            /** @var PrivateCostCenter $costCenter */
            $costCenter = $customer->costCenters()->first();
            if (!$costCenter) {
                continue;
            }
            $ddaAmount = $costCenter->inc_tax / $customer->getPeriodInteger();
            $costCenterName = TemplateGenerator::getCorrectString($costCenter->name);

            $prebuildsValues = [
                'name' => [],
                'qty' => [],
                'tax' => [],
                'ex_tax' => [],
                'inc_tax' => [],
                'sum_ex_tax' => 0,
                'sum_inc_tax' => 0,
                'sum_tax' => 0,
            ];
            $prebuilds = $costCenter->prebuilds()->get();
            foreach ($prebuilds as $prebuild) {
                $prebuildsValues['name'][] = TemplateGenerator::getCorrectString($prebuild->name);
                $prebuildsValues['qty'][] = TemplateGenerator::getCorrectString($prebuild->qty);
                $prebuildsValues['ex_tax'][] = TemplateGenerator::getCorrectString($prebuild->ex_tax);
                $prebuildsValues['inc_tax'][] = TemplateGenerator::getCorrectString($prebuild->inc_tax);
                $prebuildsValues['tax'][] = TemplateGenerator::getCorrectString($prebuild->inc_tax - $prebuild->ex_tax);
                $prebuildsValues['sum_ex_tax'] += $prebuild->ex_tax;
                $prebuildsValues['sum_inc_tax'] += $prebuild->inc_tax;
            }
            $prebuildsValues['sum_tax'] = $prebuildsValues['sum_inc_tax'] - $prebuildsValues['sum_ex_tax'];
            $prebuildsValues = array_map(function ($prebuild) {
                return is_array($prebuild) ? implode('</w:t><w:br/><w:t>', $prebuild) : $prebuild;
            }, $prebuildsValues);
            $this
                ->fillSiteInfo(
                    $templateProcessor,
                    $customer->site_address,
                    $customer->site_city,
                    $customer->site_state,
                    $customer->site_postal_code
                )
                ->fillPrebuilds($templateProcessor, $prebuildsValues)
                ->fillCostCenter($templateProcessor, $costCenterName, $ddaAmount);
        }
        return $this;
    }

    public function fillDefaultValues(TemplateProcessor $templateProcessor, $customerId, $nextRecurringDate)
    {
        $templateProcessor->setValue('TodayDate', Date::getFormattedToday());
        $templateProcessor->setValue('CustomerID', TemplateGenerator::getCorrectString($customerId));
        $templateProcessor->setValue('NextRecurringDate', TemplateGenerator::getCorrectString($nextRecurringDate));
        $templateProcessor->setValue('NextRecurringDate', TemplateGenerator::getCorrectString($nextRecurringDate));
        return $this;
    }

    public function fillHeadingCustomerInfo(
        TemplateProcessor $templateProcessor,
        $contactName,
        $address,
        $city,
        $county,
        $postCode
    )
    {

        $address = TemplateGenerator::getCorrectString($address);
        $exploded = explode(',', $address);
        $address1 = array_shift($exploded) ?? '';
        $address2 = trim(implode(', ', $exploded));

        $variables = [
            'ContactName',
            'Address',
            'Address2',
            'City',
            'County',
            'Postcode'
        ];

        $values = [
            TemplateGenerator::getCorrectString($contactName),
            $address1,
            $address2,
            TemplateGenerator::getCorrectString($city),
            TemplateGenerator::getCorrectString($county),
            TemplateGenerator::getCorrectStringUppercased($postCode)
        ];

        TemplateGenerator::fillValuesIfExistWithLimit(
            $templateProcessor,
            $variables,
            $values,
            999
        );
        return $this;
    }

    public function fillSiteInfo(
        TemplateProcessor $templateProcessor,
        $address,
        $city,
        $county,
        $postCode
    )
    {

        $address = TemplateGenerator::getCorrectString($address);
        $exploded = explode(',', $address);
        $address1 = array_shift($exploded) ?? '';
        $address2 = trim(implode(', ', $exploded));

        $variables = [
            'SiteAddress',
            'SiteAddress2',
            'SiteCity',
            'SiteCounty',
            'SitePostcode',
        ];

        $values = [
            $address1,
            $address2,
            TemplateGenerator::getCorrectString($city),
            TemplateGenerator::getCorrectString($county),
            TemplateGenerator::getCorrectStringUppercased($postCode)
        ];

        TemplateGenerator::fillValuesIfExistWithLimit(
            $templateProcessor,
            $variables,
            $values
        );
        return $this;
    }

    public function fillPrebuilds(TemplateProcessor $templateProcessor, $prebuilds)
    {
        $templateProcessor->setValue('PrebuildName', $prebuilds['name'], 1);
        $templateProcessor->setValue('PrebuildQty', $prebuilds['qty'], 1);
        $templateProcessor->setValue('PrebuildExTax', $prebuilds['ex_tax'], 1);
        $templateProcessor->setValue('PrebuildTax', $prebuilds['tax'], 1);
        $templateProcessor->setValue('PrebuildIncTax', $prebuilds['inc_tax'], 1);
        $templateProcessor->setValue('TotalExTax', $prebuilds['sum_ex_tax'], 1);
        $templateProcessor->setValue('TotalIncTax', $prebuilds['sum_inc_tax'], 1);
        $templateProcessor->setValue('TotalTax', $prebuilds['sum_tax'], 1);
        return $this;
    }

    public function fillCostCenter(TemplateProcessor $templateProcessor, $costCenterName, $ddaAmount)
    {
        $templateProcessor->setValue('CostCenterName', $costCenterName, 1);
        $templateProcessor->setValue('DDAmount', $ddaAmount);
    }

}