<?php

namespace App\Service;


use App\Helpers\Date;
use App\Models\CostCenter;
use App\Models\PrivateAsset;
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

        $isProject = $customer->recurring_type == PrivateCustomer::PROJECT;
        $isDebit = $customer->type == PrivateCustomer::DEBIT;
        $customers = [$customer];


        if ($isProject) {
            $templateProcessor->cloneBlock('SITE_BLOCK', 0);

            $costCentersCount = 0;
            foreach ($customers as $c) {
                $costCentersCount += $c->costCenters()->count();
            }
            $templateProcessor->cloneBlock('PROJECT_BLOCK', $costCentersCount, 1, true);
            $templateProcessor->cloneBlock('FINALS', 1);
            $templateProcessor->cloneBlock('PROJECT_TITLE_BLOCK', 1);
        } else {
            $templateProcessor->cloneBlock('PROJECT_BLOCK', 0);
            $templateProcessor->cloneBlock('PROJECT_TITLE_BLOCK', 0);
            $countCustomers = count($customers);
            $templateProcessor->cloneBlock('FINALS', $countCustomers > 1 ? 1 : 0);
            $templateProcessor->cloneBlock('SITE_BLOCK', $countCustomers);
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
                $customers,
                $isProject
            )
            ->fillDebitInfo(
                $templateProcessor,
                $customer
            );
        $today = new \DateTime();
        $name = $isDebit ? 'Debit' : 'Annual';
        $newFile = $customer->customer_id.'.'.$name.'.'.$today->format(
                'Y-m-d'
            ).'.'.$customer->recurring_invoice_id.'.docx';

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
        $templateProcessor->setValue('DDPaymentDate', TemplateGenerator::getCorrectStringWithoutUcwords($customer->getDirectDate()));
    }

    public function fillCostCenterTables(TemplateProcessor $templateProcessor, $customers, $isProject)
    {
        $finalValues = [
            'sum_ex_tax' => 0,
            'sum_inc_tax' => 0,
            'sum_tax' => 0
        ];
        /** @var PrivateCustomer $customer */
        $i = 0;
        $previousSectionId = null;
        foreach ($customers as $customer) {
            /** @var PrivateCostCenter $costCenter */
            $costCenters = $customer->costCenters()->get();
            if (!count($costCenters)) {
                continue;
            }
            foreach ($costCenters as $costCenter) {
                $i++;
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
                $prebuilds = $costCenter->assets()->get();
                $totalPrebuilds = count($prebuilds);
                $isNeedSection = (($previousSectionId === null && $costCenter->section_id !== null)
                    || $previousSectionId !== $costCenter->section_id) && $customer->recurring_type == PrivateCustomer::PROJECT;
                if ($isNeedSection) {
                    $totalPrebuilds++;
                    $previousSectionId = $costCenter->section_id;
                }
                $templateProcessor->cloneBlock('PREBUILDS_BLOCK', $totalPrebuilds, 1);
                $templateProcessor->cloneBlock('PREBUILDS_BLOCK#' . $i, $totalPrebuilds, 1);
                if ($isNeedSection) {
                    $prebuildValues = [];
                    $prebuildValues['name'] = TemplateGenerator::getCorrectString($costCenter->section_name);
                    $prebuildValues['qty'] = '';
                    $prebuildValues['ex_tax'] = '';
                    $prebuildValues['inc_tax'] = '';
                    $prebuildValues['tax'] = '';
                    $this->fillPrebuildRaw($templateProcessor, $prebuildValues, $i);
                }
                if ($isProject && count($prebuilds)) {
                    $firstAsset = $prebuilds->first();
                    $prebuildsValues['name'][] = TemplateGenerator::getCorrectString($firstAsset['section_name']);
                    $prebuildsValues['qty'][] = '';
                    $prebuildsValues['ex_tax'][] = '';
                    $prebuildsValues['inc_tax'][] = '';
                    $prebuildsValues['tax'][] = '';
                }
                foreach ($prebuilds as $prebuild) {
                    $prebuildValues = [];
                    $isDiscount = $prebuild->type === PrivateAsset::DISCOUNT_TYPE;
                    $prebuildsValues['name'][] = $prebuildValues['name'] = TemplateGenerator::getCorrectString(
                        $prebuild->name
                    );
                    $prebuildsValues['qty'][] = $prebuildValues['qty'] =
                        !$isDiscount
                            ? number_format((float)TemplateGenerator::getCorrectString($prebuild->qty), 2)
                            : '';

                    $prebuildsValues['ex_tax'][] = $prebuildValues['ex_tax'] = number_format(
                        (float)TemplateGenerator::getCorrectString($prebuild->ex_tax),
                        2
                    );
                    $tax = round($prebuild->ex_tax * 0.2, 2);
                    $prebuildsValues['inc_tax'][] = $prebuildValues['inc_tax'] = number_format(
                        (float)TemplateGenerator::getCorrectString($tax + $prebuild->ex_tax),
                        2
                    );
                    $prebuildsValues['tax'][] = $prebuildValues['tax'] = number_format(
                        (float)TemplateGenerator::getCorrectString($tax),
                        2
                    );

                    $prebuildsValues['sum_inc_tax'] += ($isDiscount ? -1 : 1) * ($tax + $prebuild->ex_tax);
                    $prebuildsValues['sum_ex_tax'] += ($isDiscount ? -1 : 1) * $prebuild->ex_tax;
                    $prebuildsValues['sum_tax'] += ($isDiscount ? -1 : 1) * $tax;


                    $this->fillPrebuildRaw($templateProcessor, $prebuildValues, $i);
                }
                $prebuildsValues['sum_tax'] = $prebuildsValues['sum_inc_tax'] - $prebuildsValues['sum_ex_tax'];
                $finalValues['sum_ex_tax'] += $prebuildsValues['sum_ex_tax'];
                $finalValues['sum_inc_tax'] += $prebuildsValues['sum_inc_tax'];
                $finalValues['sum_tax'] += $prebuildsValues['sum_tax'];
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
                    ->fillPrebuilds($templateProcessor, $costCenter, $i)
                    ->fillCostCenter($templateProcessor, $costCenterName)
                ;
            }

        }
        $ddaAmount = ($finalValues['sum_ex_tax'] + round(
                    $finalValues['sum_ex_tax'] * 0.2,
                    2
                )) / $customer->getPeriodInteger();
        $this
            ->fillCostCenterFinal($templateProcessor, $finalValues)
            ->fillDdaAmount($templateProcessor, $ddaAmount);

        return $this;
    }

    public function fillCostCenterFinal(TemplateProcessor $templateProcessor, $finalValues)
    {
        $tax = round($finalValues['sum_ex_tax'] * 0.2, 2);
        $templateProcessor->setValue('FinalTotalExTax', TemplateGenerator::getFloatValue($finalValues['sum_ex_tax']), 1);
        $templateProcessor->setValue('FinalTotalTax', TemplateGenerator::getFloatValue($tax), 1);
        $templateProcessor->setValue(
            'FinalTotalIncTax',
            TemplateGenerator::getFloatValue($finalValues['sum_ex_tax'] + $tax),
            1
        );
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

    public function fillPrebuildRaw(TemplateProcessor $templateProcessor, $prebuild, $i)
    {
        $templateProcessor->setValue('PrebuildName', $prebuild['name'], 1);
        $templateProcessor->setValue('PrebuildQty', $prebuild['qty'], 1);
        $templateProcessor->setValue('PrebuildExTax', $prebuild['ex_tax'], 1);
        $templateProcessor->setValue('PrebuildTax', $prebuild['tax'], 1);
        $templateProcessor->setValue('PrebuildIncTax', $prebuild['inc_tax'], 1);

        $templateProcessor->setValue('PrebuildName#' . $i, $prebuild['name'], 1);
        $templateProcessor->setValue('PrebuildQty#' . $i, $prebuild['qty'], 1);
        $templateProcessor->setValue('PrebuildExTax#' . $i, $prebuild['ex_tax'], 1);
        $templateProcessor->setValue('PrebuildTax#' . $i, $prebuild['tax'], 1);
        $templateProcessor->setValue('PrebuildIncTax#' . $i, $prebuild['inc_tax'], 1);
    }
    public function fillPrebuilds(TemplateProcessor $templateProcessor, PrivateCostCenter $costCenter, $i)
    {

        $templateProcessor->setValue('TotalExTax', number_format($costCenter->ex_tax, 2), 1);
        $templateProcessor->setValue('TotalIncTax', number_format($costCenter->inc_tax, 2), 1);
        $templateProcessor->setValue('TotalTax', number_format($costCenter->tax, 2), 1);


        $templateProcessor->setValue('TotalExTax#' . $i, number_format($costCenter->ex_tax, 2), 1);
        $templateProcessor->setValue('TotalIncTax#'.$i, number_format($costCenter->inc_tax, 2), 1);
        $templateProcessor->setValue('TotalTax#'.$i, number_format($costCenter->tax, 2), 1);

        return $this;
    }

    public function fillCostCenter(TemplateProcessor $templateProcessor, $costCenterName)
    {
        $templateProcessor->setValue('CostCenterName', $costCenterName, 1);
        return $this;
    }

    public function fillDdaAmount(TemplateProcessor $templateProcessor, $ddaAmount)
    {
        $templateProcessor->setValue('DDAmount', TemplateGenerator::getFloatValue($ddaAmount));
        return $this;
    }

}
