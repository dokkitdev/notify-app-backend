<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateCustomer;
use App\Service\Exceptions\MessageException;
use App\Service\PrivateService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NewPrivateController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $customers = $this->getCustomers(PrivateCustomer::ANNUAL, $limit);
        foreach ($customers as $customer) {
            $invoices = [];

            if ($customer->recurring_type == PrivateCustomer::SERVICE) {
                $allCustomers = PrivateCustomer::where('is_processed', false)
                    ->where('next_recurring_date', $customer->next_recurring_date)
                    ->where('type', PrivateCustomer::ANNUAL)
                    ->where('recurring_type', PrivateCustomer::SERVICE)
                    ->where('customer_id', $customer->customer_id)
                    ->get();
                foreach ($allCustomers as $c) {
                    $invoices[] = $c->recurring_invoice_id;
                }
                $invoices = array_unique($invoices);
                $invoices = implode(',', $invoices);
                $customer->invoices = $invoices;
            } else {
                $customer->invoices = $customer->recurring_invoice_id;
            }
        }
        return view('admin.new_private.private', [
            'customers' => $customers,
            'limit' => $limit,
            'title' => 'Private Contract Letters (Annual payment)',
        ]);
    }

    public function debitIndex(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $customers = $this->getCustomers(PrivateCustomer::DEBIT, $limit);
        foreach ($customers as $customer) {
            $customer->invoices = $customer->recurring_invoice_id;
        }
        return view('admin.new_private.private', [
            'customers' => $customers,
            'limit' => $limit,
            'title' => 'Private Contract Letters (Direct Debit)',
        ]);
    }

    private function getCustomers($type, $limit)
    {
        $customers = PrivateCustomer::where('is_processed', false)
            ->select('id', 'customer_id', 'next_recurring_date', 'recurring_type')
            ->where('type', $type)
            ->get();
        $filteredCustomers = [];
        if ($type === PrivateCustomer::ANNUAL) {
            foreach ($customers as $customer) {
                /**
                 * @var $id
                 * @var $customer_id
                 * @var $next_recurring_date
                 * @var $recurring_type
                 */
                extract($customer->getAttributes());
                if (!isset($filteredCustomers[$customer_id])) {
                    $filteredCustomers[$customer_id] = [
                        PrivateCustomer::PROJECT => [],
                        PrivateCustomer::SERVICE => null,
                    ];
                }
                if ($recurring_type == PrivateCustomer::PROJECT) {
                    $filteredCustomers[$customer_id][PrivateCustomer::PROJECT][] = $id;
                } else if ($filteredCustomers[$customer_id][PrivateCustomer::SERVICE] === null && $recurring_type == PrivateCustomer::SERVICE) {
                    $filteredCustomers[$customer_id][PrivateCustomer::SERVICE] = $id;
                }
            }
            $ids = [];
            foreach ($filteredCustomers as $filteredCustomer) {
                $ids = array_merge($ids, $filteredCustomer[PrivateCustomer::PROJECT]);
                $ids[] = $filteredCustomer[PrivateCustomer::SERVICE];
            }
        } else {
            $ids = array_map(
                function ($customer) {
                return $customer->id;
            }, iterator_to_array($customers));
        }
        return PrivateCustomer::whereIn('id', $ids)
            ->paginate($limit);
    }


    public function viewPdf($id, Request $request)
    {

        $customer = PrivateCustomer::find($id);
        if (!$customer) {
            return redirect()->route('private.all');
        }
        PrivateService::generateFilesForCustomer($customer);
        $pdf = $customer->pdf;
        $pdfFolder = Config::get('constants.storage_pdf');
        return response()->file($pdfFolder . '/' . $pdf);
    }

    public function generate(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $customersId = $request->request->get('private');
        try {
            $message = PrivateService::startProcessing($customersId);
        } catch (MessageException $exception) {
            $message = $exception->getDescription();
        }
        return redirect()->back()->with($message);
    }

}
