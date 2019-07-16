<?php

namespace App\Service\Upload;

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
            $nextRecurringDate = new \DateTime('+28 day');
            $nextRecurringDate = $nextRecurringDate->format('Y-m-d');
        } catch (\Exception $e) {
            dump($e->getMessage());
            exit(1);
        }
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
            ->where('next_recurring_date', 'LIKE', $recurringDate->format('Y') . '%')
            ->count();
        dump($customerId);
        if ($countCustomerForProvidedYear) {
            dump('more than one customer for year for ' . $recurringInvoiceId);
            return;
        }

        $privateCustomer = PrivateCustomer::create([
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
            'type' => $customFieldValue,
            'is_processed'=> false,
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
        $costCenterId = $costCenter->ID;
        $preBuilds = $this->simpro->getRequest('get', "/api/v1.0/companies/0/recurringInvoices/${recurringInvoiceId}/sections/${sectionId}/costCenters/${costCenterId}/prebuilds/");
        if ($preBuilds) {
            foreach ($preBuilds as $preBuild) {
                $p = $privateCustomer->prebuilds()->create([
                    'name' => $preBuild->Prebuild->Name ?? null,
                    'qty' => $preBuild->Total->Qty ?? null,
                    'ex_tax' => $preBuild->Total->Amount->ExTax ?? null,
                    'inc_tax' => $preBuild->Total->Amount->IncTax ?? null,
                ]);
                $cc->prebuilds()->save($p);
            }
        }
    }
}
