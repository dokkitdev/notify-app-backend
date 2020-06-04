<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateCustomer;
use App\Service\Exceptions\MessageException;
use App\Service\PrivateService;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

class NewPrivateController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        $customers = $this->getCustomers(PrivateCustomer::ANNUAL, $limit, $sort, $direction);
        return view('admin.new_private.private', [
            'customers' => $customers,
            'limit' => $limit,
            'title' => 'Private Contract Letters (Annual payment)',
        ]);
    }

    public function debitIndex(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'asc';
        $customers = $this->getCustomers(PrivateCustomer::DEBIT, $limit, $sort, $direction);
        foreach ($customers as $customer) {
            $customer->invoices = $customer->recurring_invoice_id;
        }
        return view('admin.new_private.private', [
            'customers' => $customers,
            'limit' => $limit,
            'title' => 'Private Contract Letters (Direct Debit)',
        ]);
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
