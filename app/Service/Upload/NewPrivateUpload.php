<?php

namespace App\Service\Upload;

use App\Models\ParsingLog;
use App\Models\PrivateAsset;
use App\Models\PrivateCustomer;
use App\Service\simProRequestService;

class NewPrivateUpload
{
    /** @var simProRequestService */
    private $simpro;

    private $totalCount;
    private $totalSuccess;
    private $reasons = [];
    private $recurringIds = [];

    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }

    public function rerapseByRecurringInvoiceId($recurringInvoiceId)
    {
	set_time_limit(0);
        $recurringInvoice = $this->simpro->getRequest(
            'get',
	            '/api/v1.0/companies/0/recurringInvoices/'.$recurringInvoiceId
        );
        if ($recurringInvoice) {
            $this->processRecurringInvoice($recurringInvoice);
            return true;
        }
        return false;
    }


    public function startToParse(): void
    {
        foreach (
            [
                new \DateTime('+17 day'),
                new \DateTime('+18 day'),
                new \DateTime('+19 day'),
                new \DateTime('+20 day'),
                new \DateTime('+21 day'),
                new \DateTime('+22 day'),
                new \DateTime('+23 day'),
                new \DateTime('+24 day'),
                new \DateTime('+25 day'),
                new \DateTime('+26 day'),
                new \DateTime('+27 day'),
                new \DateTime('+28 day'),
                new \DateTime('+29 day'),

            ] as $nextRecurringDate
        ) {
            $nextRecurringDate = $nextRecurringDate->format('Y-m-d');
            $pages = $this->simpro->getRequestPage(
                'get',
                "/api/v1.0/companies/0/recurringInvoices/?NextRecurringDate=${nextRecurringDate}&Removed=false"
            );
//            dump(count($pages), $nextRecurringDate);
            $this->totalCount = $this->simpro->result_count;
            $this->totalSuccess = 0;
            $this->reasons = [];
            $this->recurringIds = [];
            foreach ($pages as $page) {
                $this->parseRecurringPage($page);
            }

            $pagesToRemove = $this->simpro->getRequestPage(
                'get',
                "/api/v1.0/companies/0/recurringInvoices/?NextRecurringDate=${nextRecurringDate}&Removed=true"
            );
            $startYear = Date('Y').'-01-01 00:00:00';
            foreach ($pagesToRemove as $page) {
                $recurringInvoices = $this->simpro->getRequest('get', $page);
                if (!$recurringInvoices || !count($recurringInvoices)) {
                    continue;
                }
                foreach ($recurringInvoices as $recurringInvoice) {
                    $recurringInvoiceId = $recurringInvoice->ID;

                    $recurringInvoice = $this->simpro->getRequest(
                        'get',
                        '/api/v1.0/companies/0/recurringInvoices/'.$recurringInvoiceId
                    );

                    $recurringDate = $recurringInvoice->NextRecurringDate ?? null;
                    $recurringDate = \DateTime::createFromFormat('Y-m-d', $recurringDate);
                    $customerId = $recurringInvoice->Customer->ID ?? null;

                    if (!$customerId || !$recurringDate) {
                        continue;
                    }

//                    dump(
//                        "Need  to  remove  customer - {$customerId}, recurring_invoice_id - {$recurringInvoiceId}, and date {$startYear}"
//                    );
                    PrivateCustomer::where('customer_id', $customerId)
                        ->where('recurring_invoice_id', $recurringInvoiceId ?: 0)
                        ->where('next_recurring_date', '>=', $startYear)
                        ->where('is_processed', false)
                        ->delete();
                }
            }


            ParsingLog::create(
                [
                    'type' => ParsingLog::PRIVATE_TYPE,
                    'total_count' => $this->totalCount,
                    'total_success' => $this->totalSuccess,
                    'reasons' => $this->reasons,
                    'ids' => $this->recurringIds,
                    'parsing_date' => $nextRecurringDate,
                ]
            );
        }
    }

    public function parseRecurringPage($page): void
    {
        $recurringInvoices = $this->simpro->getRequest('get', $page);
        if (!$recurringInvoices || !count($recurringInvoices)) {
            return;
        }
        foreach ($recurringInvoices as $recurringInvoice) {
            $this->processRecurringInvoice($recurringInvoice);
        }
    }

    private $isCompanyCustomer = false;
    private $isIndividualCustomer = false;
    private $period;
    private $payerReference;
    private $payerAccountName;
    private $directDate;
    private $month;

    public function processRecurringInvoice($recurringInvoice): void
    {
        $this->month = null;
        $recurringInvoiceId = $recurringInvoice->ID;
        $this->recurringIds[] = $recurringInvoiceId;
        $recurringInvoice = $this->simpro->getRequest(
            'get',
            '/api/v1.0/companies/0/recurringInvoices/'.$recurringInvoiceId
        );

//        dump('start to parse'.$recurringInvoiceId);
        if (!$recurringInvoice) {
            $this->reasons[] = $recurringInvoiceId.'. No recurring invoice found';
//            dump('no recurring invoice for '.$recurringInvoiceId);

            return;
        }
        if (!$recurringInvoice->CustomFields) {
            $this->reasons[] = $recurringInvoiceId.'. No custom fields found';
//            dump('no custom fields '.$recurringInvoiceId);

            return;
        }
        $customFieldValues = ['Annual payment', 'Direct Debit'];
        $customFieldValue = null;
        $this->period = $this->payerReference = $this->payerAccountName = $this->directDate = null;
        foreach ($recurringInvoice->CustomFields as $customField) {
            if (
                $customField->CustomField->ID === 4
                && $customField->Value !== null
                && in_array($customField->Value, $customFieldValues, true)
            ) {
                $customFieldValue = $customField->Value;
            }
            if ($customField->CustomField->ID === 3) {
                $this->period = $customField->Value;
            }
            if ($customField->CustomField->ID === 6) {
                $this->payerReference = $customField->Value;
            }
            if ($customField->CustomField->ID === 5) {
                $this->directDate = $customField->Value;
            }

            if ($customField->CustomField->ID === 7) {
                $this->payerAccountName = $customField->Value;
            }

            if ($customField->CustomField->ID === 8) {
                $this->month = $customField->Value;
            }
        }
        if (!$customFieldValue) {
            $this->reasons[] = $recurringInvoiceId.'. No custom field found';
//            dump('no custom field value for '.$recurringInvoiceId);

            return;
        }

        $customerId = $recurringInvoice->Customer->ID;
        $this->isCompanyCustomer = $this->isIndividualCustomer = false;
        $customer = $this->getCompanyCustomer($customerId);
        if (!$customer) {
            $customer = $this->getIndividualCustomer($customerId);
            $this->isIndividualCustomer = true;
        } else {
            $this->isCompanyCustomer = true;
        }

        if (!$customer) {
            $this->reasons[] = $recurringInvoiceId.'. No customer found for recurring invoice';
//            dump('no Customer for '.$recurringInvoiceId);

            return;
        }

        $siteId = $recurringInvoice->Site->ID;
        $siteInfo = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/'.$siteId);

        $customerId = $recurringInvoice->Customer->ID ?? null;
        $recurringDate = $recurringInvoice->NextRecurringDate ?? null;
        $recurringDate = \DateTime::createFromFormat('Y-m-d', $recurringDate);
        if (!$customerId || !$recurringDate) {
            $this->reasons[] = $recurringInvoiceId.'. No customerId or recurring date found';
//            dump('no CustomerId or recurring date for '.$recurringInvoiceId);

            return;
        }

        PrivateCustomer::where('customer_id', $customerId)
            ->where('recurring_invoice_id', $recurringInvoiceId ?: 0)
            ->where('next_recurring_date', '>=', Date('Y').'-01-01 00:00:00')
            ->where('is_processed', false)
            ->delete();
        $countCustomerForProvidedYear = PrivateCustomer::where('customer_id', $customerId)
            ->where('recurring_invoice_id', $recurringInvoiceId ?: 0)
            ->where('next_recurring_date', 'LIKE', $recurringDate->format('Y').'%')
            ->count();
        if ($countCustomerForProvidedYear) {
            $this->totalSuccess++;
//            dump('This recurring invoice for customer already exist '.$recurringInvoiceId);

            return;
        }

        $privateCustomer = PrivateCustomer::create(
            [
                'recurring_type' => $recurringInvoice->Type,
                'recurring_invoice_id' => $recurringInvoice->ID ?? null,
                'next_recurring_date' => $recurringDate->format('Y-m-d'),
                'customer_id' => $customerId,
                'site_name' => $recurringInvoice->Site->Name ?? null,
                'company_name' => $customer->CompanyName ?? null,
                'customer_title' => $customer->Title ?? null,
                'customer_given_name' => $customer->GivenName ?? null,
                'customer_family_name' => $customer->FamilyName ?? null,
                'customer_address' => $customer->Address->Address ?? null,
                'customer_city' => $customer->Address->City ?? null,
                'customer_state' => $customer->Address->State ?? null,
                'customer_postal_code' => $customer->Address->PostalCode ?? null,
                'site_address' => $siteInfo->Address->Address ?? null,
                'site_city' => $siteInfo->Address->City ?? null,
                'site_state' => $siteInfo->Address->State ?? null,
                'site_postal_code' => $siteInfo->Address->PostalCode ?? null,
                'is_company' => $this->isCompanyCustomer,
                'period' => $this->period,
                'payer_reference' => $this->payerReference,
                'payer_account_name' => $this->payerAccountName,
                'direct_date' => $this->directDate,
                'direct_month' => $this->month,
                'type' => $customFieldValue,
                'is_processed' => false,
            ]
        );

        $this->totalSuccess++;

        $recurringInvoiceSections = $this->simpro->getRequest(
            'get',
            '/api/v1.0/companies/0/recurringInvoices/'.$recurringInvoiceId.'/sections/?pageSize=100'
        );
        if ($recurringInvoiceSections) {
            foreach ($recurringInvoiceSections as $recurringInvoiceSection) {
                $this->processRecurringInvoiceSection(
                    $recurringInvoice,
                    $recurringInvoiceSection,
                    $privateCustomer
                );
            }
        }
    }

    public function getCompanyCustomer($id)
    {
        return $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/companies/'.$id);
    }


    public function getIndividualCustomer($id)
    {
        return $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/individuals/'.$id);
    }

    public function processRecurringInvoiceSection(
        $recurringInvoice,
        $section,
        $privateCustomer
    ): void {
        $recurringInvoiceId = $recurringInvoice->ID;
        $sectionId = $section->ID;
        $costCenters = $this->simpro->getRequest(
            'get',
            "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/"
        );
        if ($costCenters) {
            foreach ($costCenters as $costCenter) {
                $this->processCostCenter(
                    $recurringInvoice,
                    $section,
                    $costCenter,
                    $privateCustomer
                );
            }
        }
    }

    public function processCostCenter(
        $recurringInvoice,
        $section,
        $costCenter,
        $privateCustomer
    ): void {
        $recurringInvoiceId = $recurringInvoice->ID;
        $sectionId = $section->ID;
        $sectionName = $section->Name;


        $cc = $privateCustomer->costCenters()->create(
            [
                'name' => $costCenter->CostCenter->Name ?? null,
                'ex_tax' => $costCenter->Total->ExTax ?? null,
                'tax' => $costCenter->Total->Tax ?? null,
                'inc_tax' => $costCenter->Total->IncTax ?? null,
                'section_id' => $sectionId,
                'section_name' => $sectionName,
            ]
        );

        $costCenterId = $costCenter->ID;
        $catalogs = $this->simpro->getRequest(
            'get',
            "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}/catalogs/"
        );
        $this->createAssets(
            $privateCustomer,
            $sectionId,
            $sectionName,
            $cc,
            $catalogs,
            PrivateAsset::CATALOG_TYPE,
            'Catalog'
        );
        $offs = $this->simpro->getRequest(
            'get',
            "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}/oneOffs/"
        );
        $this->createAssets(
            $privateCustomer,
            $sectionId,
            $sectionName,
            $cc,
            $offs,
            PrivateAsset::ONEOFF_TYPE,
            'One Off'
        );
        $preBuilds = $this->simpro->getRequest(
            'get',
            "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}/prebuilds/"
        );
        $this->createAssets(
            $privateCustomer,
            $sectionId,
            $sectionName,
            $cc,
            $preBuilds,
            PrivateAsset::PREBUILD_TYPE
        );
        $costCenter = $this->simpro->getRequest(
            'get',
            "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}"
        );
//        dump('offs', $offs, 'catalogs', $catalogs, 'Discount', $costCenter->Totals->Discount);
        $exTax = $costCenter->Totals->Discount ?? 0; //3.02
        if ($exTax) {
            $incTax = $exTax + Round($exTax * 0.2, 2);
            $asset = $privateCustomer->assets()->create(
                [
                    'section_id' => $sectionId,
                    'section_name' => $sectionName,
                    'name' => 'Discount',
                    'qty' => null,
                    'ex_tax' => $exTax,
                    'inc_tax' => $incTax,
                    'type' => PrivateAsset::DISCOUNT_TYPE,
                ]
            );
            $cc->assets()->save($asset);
        }
    }

    public function createAssets(
        $privateCustomer,
        $sectionId,
        $sectionName,
        $costCenter,
        $assets,
        $assetType,
        $type = 'Prebuild'
    ) {
        if (!($assets && count($assets))) {
            return;
        }


        foreach ($assets as $asset) {
            if ($assetType === PrivateAsset::ONEOFF_TYPE) {
                $name = $asset->Description ?? null;
            } else {
                $name = $asset->{$type}->Name ?? null;
            }

            $p = $privateCustomer->assets()->create(
                [
                    'section_id' => $sectionId,
                    'section_name' => $sectionName,
                    'name' => $name,
                    'qty' => $asset->Total->Qty ?? null,
                    'ex_tax' => $asset->Total->Amount->ExTax ?? null,
                    'inc_tax' => $asset->Total->Amount->IncTax ?? null,
                    'type' => $assetType,
                ]
            );
            $costCenter->assets()->save($p);
        }
    }
}
