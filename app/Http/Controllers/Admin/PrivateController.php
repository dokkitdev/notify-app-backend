<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Logs;
use App\Models\Contract;
use App\Models\Customer;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrivateController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;

        $week1 = new \DateTime('+1 week');
        $week4 = new \DateTime('+4 week');
        $week8 = new \DateTime('+8 week');

        $week1Minus2Day = (clone $week1)->modify('-2 day')->format('Y-m-d 00:00:00');
        $week4Minus2Day = (clone $week4)->modify('-2 day')->format('Y-m-d 00:00:00');
        $week8Minus2Day = (clone $week8)->modify('-2 day')->format('Y-m-d 00:00:00');
        $week1 = $week1->format('Y-m-d 23:59:59');
        $week4 = $week4->format('Y-m-d 23:59:59');
        $week8 = $week8->format('Y-m-d 23:59:59');

        $customers = array_map(function ($el) {
            return $el->id;
        }, DB::select("
            SELECT DISTINCT cus.id FROM n_customers cus
LEFT JOIN n_contracts con ON cus.id = con.customer_id
WHERE 
(con.end_date >= :week1minus AND con.end_date <= :week1 AND con.is_processed_1 IS NULL)
OR (con.end_date >= :week2minus AND con.end_date <= :week2 AND con.is_processed_4 IS NULL)
OR (con.end_date >= :week3minus AND con.end_date <= :week3 AND con.is_processed_8 IS NULL)

        ", [
            $week1Minus2Day,
            $week1,
            $week4Minus2Day,
            $week4,
            $week8Minus2Day,
            $week8,
        ]));

        $customers = Customer::whereIn('id', $customers)
            ->paginate($limit);

        $i = 0;
        foreach ($customers as $c) {
            $contractNum = [];
            $assets = [];
            foreach ($c->contracts as $contract) {
                $temp_end = $contract->end_date;
                $is_processed_1 = $contract->is_processed_1;
                $is_processed_4 = $contract->is_processed_4;
                $is_processed_8 = $contract->is_processed_8;
                if (
                    ($temp_end >= $week1Minus2Day && $temp_end <= $week1 && $is_processed_1 === null)
                    OR ($temp_end >= $week4Minus2Day && $temp_end <= $week4 && $is_processed_4 === null)
                    OR ($temp_end >= $week8Minus2Day && $temp_end <= $week8 && $is_processed_8 === null)
                ) {
                    $end_date = $contract->end_date;
                    $contractNum[] = $contract->id;
                    foreach ($contract->assets as $asset) {
                        $assets[] = $asset->value;
                    }
                }
            }
            if (count($assets) < 1) {
                $assets[] = 'Heating Equipment';
            }
            $c->assets_filtered = array_unique($assets);
            $c->contract_numbers = $contractNum;
            $c->end_date = \DateTime::createFromFormat('Y-m-d H:i:s', $end_date)->format('Y-m-d');
            $c->type = $end_date >= $week1Minus2Day && $end_date <= $week1 && $is_processed_1 === null ? '1 week'
                : (
                    $end_date >= $week4Minus2Day && $end_date <= $week4 && $is_processed_4 === null  ? '4 week'
                        : (
                        $end_date >= $week8Minus2Day && $end_date <= $week8 && $is_processed_8 === null ? '8 week' : ''
                    )
                );
            $i++;
        }

//        $contracts = Contract::with('customer')
//            ->with('assets')
//            ->has('customer')
////            ->has('assets')
//            ->where(function ($query) use ($week1, $week4, $week8) {
//                $query
//                    ->where(function ($query) use ($week1) {
//                        $week1Minus2Day = clone $week1;
//                        $week1Minus2Day->modify('-2 day');
//                        $query
//                            ->where('end_date', '>=', $week1Minus2Day->format('Y-m-d 00:00:00'))
//                            ->Where('end_date', '<=', $week1->format('Y-m-d 23:59:59'))
//                            ->where('end_date', 'LIKE', '%' . $week1->format('Y-m-d') . '%')
//                            ->where(function ($query) {
//                                $query->where('is_processed_1', '<>', 1)
//                                    ->orWhere('is_processed_1', '=', null);
//                            });
//                    })
//                    ->orWhere(function ($query) use ($week4) {
//                        $week4Minus2Day = clone $week4;
//                        $week4Minus2Day->modify('-2 day');
//                        $query
//                            ->where('end_date', '>=', $week4Minus2Day->format('Y-m-d 00:00:00'))
//                            ->Where('end_date', '<=', $week4->format('Y-m-d 23:59:59'))
//                            ->where(function ($query) {
//                                $query->where('is_processed_4', '<>', 1)
//                                    ->orWhere('is_processed_4', '=', null);
//                            });
//                    })
//                    ->orWhere(function ($query) use ($week8) {
//                        $week8Minus2Day = clone $week8;
//                        $week8Minus2Day->modify('-2 day');
//                        $query
//                            ->where('end_date', '>=', $week8Minus2Day->format('Y-m-d 00:00:00'))
//                            ->Where('end_date', '<=', $week8->format('Y-m-d 23:59:59'))
//                            ->where(function ($query) {
//                                $query->where('is_processed_8', '<>', 1)
//                                    ->orWhere('is_processed_8', '=', null);
//                            });
//                    });
//            })
//            ->orderBy('end_date', 'ASC')
//            ->paginate($limit);
//        foreach ($contracts as $contract) {
//            $contract->customer = $contract->customer()->first();
//            $contract->asset = $contract->assets()->first();
//        }

        return view('admin.private.private', [
            'customers' => $customers,
            'limit' => $limit,
        ]);
    }

    public function viewPdf($id, Request $request)
    {
        $customer = Customer::find($id);
        if (!$customer) {
            return redirect()->route('private.all');
        }

        $generator = new TemplateGenerator();
        $docx = $generator->fillPrivateByCustomer($customer);
        if (!$docx) {
            return redirect()->route('private.all');
        }
        $pdf = $generator->generatePdfFromDocx($docx);
        $customer->docx = $docx;
        $customer->pdf = $pdf;
        $customer->save();

        $pdf_folder = \Illuminate\Support\Facades\Config::get('constants.storage_pdf');

        return response()->file($pdf_folder . '/' . $customer->pdf);
    }

    public function generate(Request $request)
    {
        $data = $request->all();
        $private = $data['private'] ?? null;
        $filled = [];
        $generator = new TemplateGenerator();
        $sim = new simProRequestService();
        if ($private && is_array($private)) {
            if (count($private) > 15) {
                foreach ($private as $h) {
                    $contract = Contract::find($h);
                    $contract->setProcess();
                    $contract->save();
                    $filled[] = $contract->id;
                }

                $log = Logs::create([
                    'customer_type' => 'Private',
                    'letters_generated' => count($filled),
                    'email_generated' => 0,
                ]);
                $filled = implode(',', $filled);
                $command = 'php ' . base_path() . '/artisan combine:pdf:private ' . $filled . ' ' . $log->id . '  > /dev/null 2>&1 &';
                $log->command = $command;
                $log->save();
                exec('php ' . base_path() . '/artisan queue:log:start > /dev/null 2>&1 &');
                return redirect()->back()->with([
                    'ok' => 'Your letters are being processed and will appear in the logs page shortly.',
                ]);
            } else {
                foreach ($private as $h) {
                    $contract = Contract::find($h);
                    /** Проверка на существование */
                    if (!$contract) {
                        unset($contract);
                        continue;
                    }
                    if (!$contract->is_proccessed) {
                        $docx = $generator->fillPrivateTemplate($contract);
                        $pdf = $generator->generatePdfFromDocx($docx);
                        $contract->docx = $docx;
                        $contract->pdf = $pdf;
//                        $sim->uploadAppointment($appointment);
                    }
                    $contract->setProcess();
                    $contract->save();
                    $filled[] = $contract;
                }
            }
        }

        if (count($filled) > 0) {
            $merged = $generator->mergePdfs($filled);
            Logs::create([
                'customer_type' => 'Private',
                'letters_generated' => count($filled),
                'email_generated' => 0,
                'pdf' => $merged
            ]);

            return redirect()->back()->with([
                'ok' => 'Private Letters has been successfull generated.',
                'merged' => $merged,
            ]);
        }

        return redirect()->back()->with([
            'error' => 'Please select at least one private letter.',
        ]);
    }

}
