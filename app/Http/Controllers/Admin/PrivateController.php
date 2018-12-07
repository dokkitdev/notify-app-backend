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

class PrivateController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;

        $week1 = new \DateTime('+1 week');
        $week4 = new \DateTime('+4 week');
        $week8 = new \DateTime('+8 week');

        $contracts = Contract::with('customer')
            ->with('assets')
            ->has('customer')
            ->has('assets')
            ->where(function ($query) use ($week1, $week4, $week8) {
                $query
                    ->where(function ($query) use ($week1) {
                        $query
                            ->where('end_date', 'LIKE', '%' . $week1->format('Y-m-d') . '%')
                            ->where(function ($query) {
                                $query->where('is_processed_1', '<>', 1)
                                    ->orWhere('is_processed_1', '=', null);
                            });
                    })
                    ->orWhere(function ($query) use ($week4) {
                        $query
                            ->where('end_date', 'LIKE', '%' . $week4->format('Y-m-d') . '%')
                            ->where(function ($query) {
                                $query->where('is_processed_4', '<>', 1)
                                    ->orWhere('is_processed_4', '=', null);
                            });
                    })
                    ->orWhere(function ($query) use ($week8) {
                        $query
                            ->where('end_date', 'LIKE', '%' . $week8->format('Y-m-d') . '%')
                            ->where(function ($query) {
                                $query->where('is_processed_8', '<>', 1)
                                    ->orWhere('is_processed_8', '=', null);
                            });
                    });
            })
            ->orderBy('end_date', 'ASC')
            ->paginate($limit);
        foreach ($contracts as $contract) {
            $contract->customer = $contract->customer()->first();
            $contract->asset = $contract->assets()->first();
        }

        return view('admin.private.private', [
            'contracts' => $contracts,
            'limit' => $limit,
        ]);
    }

    public function viewPdf($id, Request $request)
    {
        $contract = Contract::find($id);
        if (!$contract) {
            return redirect()->route('private.all');
        }

        $generator = new TemplateGenerator();
        $docx = $generator->fillPrivateTemplate($contract);
        if (!$docx) {
            return redirect()->route('private.all');
        }
        $pdf = $generator->generatePdfFromDocx($docx);
        $contract->docx = $docx;
        $contract->pdf = $pdf;
        $contract->save();

        $pdf_folder = \Illuminate\Support\Facades\Config::get('constants.storage_pdf');

        return response()->file($pdf_folder . '/' . $contract->pdf);
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
