<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logs;
use App\Models\PrivateCustomer;
use App\Service\Exceptions\MessageException;
use App\Service\PrivateService;
use App\Service\Upload\NewPrivateUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class PrivateController extends Controller
{
    public function reparse(Request $request)
    {
        $log = Logs::query()
            ->where('customer_type', 'Private')
            ->where('is_finished', '=', 0)
            ->whereNotNull('command')
            ->first();
        if ($log) {
            return redirect()->back()->with(
                [
                    'error' => 'Letters are processing please try again in a few minutes',
                ]
            );
        }

        $id = $request->request->get('id');
        $customer = PrivateCustomer::query()->where('recurring_invoice_id', $id)->first();

        if ($customer) {
            $customer->delete();
        }

        $result = (new NewPrivateUpload)
            ->rerapseByRecurringInvoiceId($id);

        return redirect()->back()->with(
            [
                $result ? 'ok' : 'error' => $result ? 'Success!' : 'Failed!',
            ]
        );
    }

    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        $customers = $this->getCustomers(PrivateCustomer::ANNUAL, $limit, $sort, $direction);
        $customers->appends($request->except(['page', '_token']));

        $log = Logs::query()
            ->where('customer_type', 'Private')
            ->where('is_finished', '=', 0)
            ->whereNotNull('command')
            ->first();

        return view(
            'admin.private.private',
            [
                'customers' => $customers,
                'limit' => $limit,
                'title' => 'Private Contract Letters (Annual payment)',
                'log' => $log,
            ]
        );
    }

    public function debitIndex(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        $customers = $this->getCustomers(PrivateCustomer::DEBIT, $limit, $sort, $direction);
        $customers->appends($request->except(['page', '_token']));
        foreach ($customers as $customer) {
            $customer->invoices = $customer->recurring_invoice_id;
        }

        $log = Logs::query()
            ->where('customer_type', 'Private')
            ->where('is_finished', '=', 0)
            ->whereNotNull('command')
            ->first();

        return view(
            'admin.private.private',
            [
                'customers' => $customers,
                'limit' => $limit,
                'title' => 'Private Contract Letters (Direct Debit)',
                'log' => $log,
            ]
        );
    }

    private function getCustomers($type, $limit, $sort = 'id', $direction = 'asc')
    {
        return PrivateCustomer::where('is_processed', false)
            ->where('type', $type)
            ->orderBy($sort, $direction)
            ->paginate($limit);
    }


    public function viewPdf($id, Request $request)
    {
        $customer = PrivateCustomer::find($id);
        if (!$customer) {
            return redirect()->route('private.all');
        }
        PrivateService::generateFilesForCustomer($customer, false);

        $pdf = $customer->pdf;
        $pdfFolder = Config::get('constants.storage_pdf');

        return response()->file($pdfFolder.'/'.$pdf);
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
