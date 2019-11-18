<?php

namespace App\Service\Upload;

use App\Models\PrivateAsset;
use App\Models\PrivateCustomer;
use App\Service\simProRequestService;

class NewPrivateUpload
{
    /** @var simProRequestService */
    private $simpro;

    public function __construct()
    {
        $this->simpro = new simProRequestService();
    }

    public function startToParse(): void
    {

        try {
            $nextRecurringDate = \DateTime::createFromFormat('Y-m-d', '2019-12-10');
            $nextRecurringDate = $nextRecurringDate->format('Y-m-d');
        } catch (\Exception $e) {
            dump($e->getMessage());
            exit(1);
        }
        dump($nextRecurringDate);
        $pages = $this->simpro->getRequestPage('get', "/api/v1.0/companies/0/recurringInvoices/?NextRecurringDate=${nextRecurringDate}");
        foreach ($pages as $page) {
            $this->parseRecurringPage($page);
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
        $recurringInvoiceId = $recurringInvoice->ID;
        $recurringInvoice = $this->simpro->getRequest('get', '/api/v1.0/companies/0/recurringInvoices/' . $recurringInvoiceId);
        dump('start to parse' . $recurringInvoiceId);
        if (!$recurringInvoice) {
            dump('no recurring invoice for ' . $recurringInvoiceId);
            return;
        }
        if (!$recurringInvoice->CustomFields) {
            dump('no custom fields ' . $recurringInvoiceId);
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
            dump('no custom field value for ' . $recurringInvoiceId);
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
            dump('no Customer for ' . $recurringInvoiceId);
            return;
        }

        $siteId = $recurringInvoice->Site->ID;
        $siteInfo = $this->simpro->getRequest('get', '/api/v1.0/companies/0/sites/' . $siteId);

        $customerId = $recurringInvoice->Customer->ID ?? null;
        $recurringDate = $recurringInvoice->NextRecurringDate ?? null;
        $recurringDate = \DateTime::createFromFormat('Y-m-d', $recurringDate);
        if (!$customerId || !$recurringDate) {
            dump('no CustomerId or recurring date for ' . $recurringInvoiceId);
            return;
        }
        $countCustomerForProvidedYear = PrivateCustomer::where('customer_id', $customerId)
            ->where('recurring_invoice_id', $recurringInvoiceId ?: 0)
            ->where('next_recurring_date', 'LIKE', $recurringDate->format('Y') . '%')
            ->count();
        if ($countCustomerForProvidedYear) {
            dump('This recurring invoice for customer already exist ' . $recurringInvoiceId);
            return;
        }

        $privateCustomer = PrivateCustomer::create([
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
        ]);


        $recurringInvoiceSections = $this->simpro->getRequest('get', '/api/v1.0/companies/0/recurringInvoices/' . $recurringInvoiceId . '/sections/');
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
        return $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/companies/' . $id);
    }


    public function getIndividualCustomer($id)
    {
        return $this->simpro->getRequest('get', '/api/v1.0/companies/0/customers/individuals/' . $id);
    }

    public function processRecurringInvoiceSection(
        $recurringInvoice,
        $section,
        $privateCustomer
    ): void
    {
        $recurringInvoiceId = $recurringInvoice->ID;
        $sectionId = $section->ID;
        $costCenters = $this->simpro->getRequest('get', "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/");
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
    ): void
    {
        $recurringInvoiceId = $recurringInvoice->ID;

        $cc = $privateCustomer->costCenters()->create([
            'name' => $costCenter->CostCenter->Name ?? null,
            'ex_tax' => $costCenter->Total->ExTax ?? null,
            'tax' => $costCenter->Total->Tax ?? null,
            'inc_tax' => $costCenter->Total->IncTax ?? null,
        ]);
        $sectionId = $section->ID;
        $sectionName = $section->Name;
        $costCenterId = $costCenter->ID;
        $catalogs = $this->simpro->getRequest('get', "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}/catalogs/");
        $this->createAssets(
            $privateCustomer,
            $sectionId,
            $sectionName,
            $cc,
            $catalogs,
            PrivateAsset::CATALOG_TYPE,
            'Catalog'
        );
        $offs = $this->simpro->getRequest('get', "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}/oneOffs/");
        $this->createAssets(
            $privateCustomer,
            $sectionId,
            $sectionName,
            $cc,
            $offs,
            PrivateAsset::ONEOFF_TYPE,
            'One Off'
        );
        $preBuilds = $this->simpro->getRequest('get', "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}/prebuilds/");
        $this->createAssets(
            $privateCustomer,
            $sectionId,
            $sectionName,
            $cc,
            $preBuilds,
            PrivateAsset::PREBUILD_TYPE
        );
        $costCenter = $this->simpro->getRequest('get', "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}");
        dump('offs', $offs, 'catalogs', $catalogs, 'Discount', $costCenter->Totals->Discount);
        $incTax = $costCenter->Totals->Discount ?? 0;
        if ($incTax) {
            $exTax = ceil($incTax * 0.8 * 100) / 100;
            $asset = $privateCustomer->assets()->create([
                'section_id' => $sectionId,
                'section_name' => $sectionName,
                'name' => 'Discount',
                'qty' => null,
                'ex_tax' => $exTax,
                'inc_tax' => $incTax,
                'type' => PrivateAsset::DISCOUNT_TYPE
            ]);
            $cc->assets()->save($asset);
        }
    }

    public function createAssets($privateCustomer, $sectionId, $sectionName, $costCenter, $assets, $assetType, $type = 'Prebuild')
    {
        if (!($assets && count($assets))) {
            return;
        }


        foreach ($assets as $asset) {

            if ($assetType === PrivateAsset::ONEOFF_TYPE) {
                $name = $asset->Description ?? null;
            } else {
                $name = $asset->{$type}->Name ?? null;
            }

            $p = $privateCustomer->assets()->create([
                'section_id' => $sectionId,
                'section_name' => $sectionName,
                'name' => $name,
                'qty' => $asset->Total->Qty ?? null,
                'ex_tax' => $asset->Total->Amount->ExTax ?? null,
                'inc_tax' => $asset->Total->Amount->IncTax ?? null,
                'type' => $assetType
            ]);
            $costCenter->assets()->save($p);
        }
    }
}
