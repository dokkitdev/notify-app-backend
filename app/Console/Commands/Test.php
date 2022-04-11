<?php

namespace App\Console\Commands;

use App\Service\simProRequestService;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    protected $customers =[];
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $simpro = new simProRequestService();
        $fp = fopen(__DIR__. '/export.csv', 'w+');
        fputcsv(
            $fp,
            [
                'Recurring Invoice ID',
                'Customer ID',
                'Company Name',
                'Address',
                'City',
                'PostalCode',
            ]
        );


        $customerPages = $simpro->getRequestPage('get', "/api/v1.0/companies/0/customers/?columns=Address,ID&limit=50");
        foreach ($customerPages as $page) {
            $customers = $simpro->getRequest('get', $page);
            foreach ($customers as $customer) {
                $this->customers[$customer->ID] = $customer;
            }
            dump('finish customers page');
        }
        dump('finish customers');

        $pages = $simpro->getRequestPage('get', "/api/v1.0/companies/0/recurringInvoices/?columns=ID,Customer&limit=50");
        foreach ($pages as $page) {
            $recurringInvoices = $simpro->getRequest('get', $page);
            foreach ($recurringInvoices as $recurringInvoice) {
                $customerId = $recurringInvoice->Customer->ID;
                $customer = $this->customers[$customerId];
                fputcsv($fp, [
                    $recurringInvoice->ID,
                    $recurringInvoice->Customer->ID,
                    $recurringInvoice->Customer->CompanyName,
                    $customer->Address->Address,
                    $customer->Address->City,
                    $customer->Address->PostalCode,
                ]);
            }
        }
        fclose($fp);

    }

}
