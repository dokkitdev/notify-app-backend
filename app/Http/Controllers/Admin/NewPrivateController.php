<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateCustomer;
use App\Service\Exceptions\MessageException;
use App\Service\PrivateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class NewPrivateController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $customers = PrivateCustomer::where('is_processed', false)
            ->where('type', PrivateCustomer::ANNUAL)
            ->groupBy('next_recurring_date')
            ->groupBy('customer_id')
            ->groupBy('type')
            ->paginate($limit);
        foreach ($customers as $customer) {
            $allCustomers = PrivateCustomer::where('is_processed', false)
                ->where('next_recurring_date', $customer->next_recurring_date)
                ->where('customer_id', $customer->customer_id)
                ->get();
            $invoices = [];
            foreach ($allCustomers as $c) {
                $invoices[] = $c->recurring_invoice_id;
            }
            $invoices = array_unique($invoices);
            $invoices = implode(',', $invoices);
            $customer->invoices = $invoices;
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
        $customers = PrivateCustomer::where('is_processed', false)
            ->where('type', PrivateCustomer::DEBIT)
            ->paginate($limit);
        foreach ($customers as $customer) {
            $customer->invoices = $customer->recurring_invoice_id;
        }
        return view('admin.new_private.private', [
            'customers' => $customers,
            'limit' => $limit,
            'title' => 'Private Contract Letters (Direct Debit)',
        ]);
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
