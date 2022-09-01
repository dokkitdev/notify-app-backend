<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HousingJob;
use App\Models\Logs;
use App\Models\Templates;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use Illuminate\Http\Request;

class HousingController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $start = $request->get('start') ?? null;
        $end = $request->get('end') ?? null;
        $sort = $request->get('sort') ?: 'due_date';
        $direction = $request->get('direction') ?: 'asc';
        if ($start) {
            $start = \DateTime::createFromFormat('d.m.Y', $start);
        } else {
            $start = new \DateTime('-4 day');
        }
        $start->setTime(0, 0, 0);


        if ($end) {
            $end = \DateTime::createFromFormat('d.m.Y', $end);
        } else {
            $end = new \DateTime('-1 day');
        }
        $end->setTime(23, 59, 59);


        $housing = HousingJob::where('tags', '<>', null)
            ->where(function ($query) {
                $query->where('is_proccessed', '<>', 1)
                    ->orWhere('is_proccessed', '=', null);
            })
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($query) use ($start, $end) {
                    $query->where('schedule_date', '>=', $start)
                        ->where('schedule_date', '<=', $end);
                })->orWhere(function ($query) {
                    $query->where('customer_id', '=', 11851)
                        ->where('tags', '=', 'No Access 2 (Letter)');
                });
            })
            ->orderBy($sort, $direction)
            ->paginate($limit);

        $housing->appends($request->except(['page', '_token']));


        $templates = [
            'No Access 1 (Letter)' => Templates::where('alias', '=', Templates::HOUSING_NO_ACCESS)->first(),
            'No Access 2 (Letter)' => Templates::where('alias', '=', Templates::HOUSING_1_ACCESS)->first(),
            'No Access 3 (Letter)' => Templates::where('alias', '=', Templates::HOUSING_2_ACCESS)->first(),
        ];


        return view('admin.housing.housing', [
            'housing' => $housing,
            'templates' => $templates,
        ]);
    }

    public function viewPdf($id, Request $request)
    {
        $housing = HousingJob::find($id);
        if (!$housing) {
            return redirect()->route('housing.all');
        }

        $generator = new TemplateGenerator();
        $docx = $generator->fillHousingTemplate($housing);
        $pdf = $generator->generatePdfFromDocx($docx);
        $housing->docx = $docx;
        $housing->pdf = $pdf;
        $housing->save();

        $pdf_folder = \Illuminate\Support\Facades\Config::get('constants.storage_pdf');

        return response()->file($pdf_folder . '/' . $housing->pdf);
    }

    public function generate(Request $request)
    {
        $data = $request->all();
        $housing = $data['housing'] ?? null;
        $filled = [];
        $generator = new TemplateGenerator();
        $sim = new simProRequestService();
        if ($housing && is_array($housing)) {
            if (count($housing) >= 1) {
                foreach ($housing as $h) {
                    $hous = HousingJob::find($h);
                    $hous->is_proccessed = true;
                    $hous->save();
                    $filled[] = $hous->id;
                }

                $log = Logs::create([
                    'customer_type' => 'Housing',
                    'letters_generated' => count($filled),
                    'email_generated' => 0,
                ]);
                $filled = implode(',', $filled);
                $command = 'php ' . base_path() . '/artisan combine:pdf:housing ' . $filled . ' ' . $log->id . '  > /dev/null 2>&1 &';
                $log->command = $command;
                $log->save();
//                exec('php ' . base_path() . '/artisan queue:log:start > /dev/null 2>&1 &');
                return redirect()->back()->with([
                    'ok' => 'Your letters are being processed and will appear in the logs page shortly.',
                ]);
            } else {
                foreach ($housing as $h) {
                    $hous = HousingJob::find($h);
                    /** Проверка на существование */
                    if (!$hous) {
                        unset($hous);
                        continue;
                    }
                    if (!$hous->is_proccessed) {
                        $docx = $generator->fillHousingTemplate($hous);
                        $pdf = $generator->generatePdfFromDocx($docx);
                        $hous->docx = $docx;
                        $hous->pdf = $pdf;
                        $sim->uploadHousing($hous);
                    }
                    $hous->is_proccessed = true;
                    $hous->save();

                    $filled[] = $hous;
                }
            }
        }

        if (count($filled) > 0) {
            $merged = $generator->mergePdfs($filled);
            Logs::create([
                'customer_type' => 'Housing',
                'letters_generated' => count($filled),
                'email_generated' => 0,
                'pdf' => $merged
            ]);

            return redirect()->back()->with([
                'ok' => 'Housing Letters has been successfull generated.',
                'merged' => $merged,
            ]);
        }

        return redirect()->back()->with([
            'error' => 'Please select at least one appointment letter.',
        ]);
    }

    public function import()
    {
        exec('php ' . base_path() . '/artisan upload:housing > /dev/null 2>&1 &');
        return redirect()->route('housing.all')->with([
            'ok' => 'Import housing are being processed and will appear in the page shortly.',
        ]);
    }

}
