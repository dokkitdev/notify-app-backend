<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Logs;
use App\Models\Contract;
use App\Models\Customer;
use App\Service\Sender\Sender;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Templates;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PrivateController extends Controller
{
    private function append($array, $contract, $type)
    {
        $end_date = \DateTime::createFromFormat('Y-m-d H:i:s', $contract->end_date);
        $end_date = $end_date->format('Y-m-d');
        if (!array_key_exists($end_date, $array)) {
            $array[$end_date] = [
                'count' => 0,
                'id' => [],
                'assets' => [],
                'type' => $type,
            ];
        }
        $array[$end_date]['count']++;
        if (trim($contract->contract_no)) {
            $array[$end_date]['id'][] = $contract->contract_no;
        }
        foreach ($contract->assets as $asset) {
            if (trim($asset->value)) {
                $array[$end_date]['assets'][] = $asset->value;
            }
        }
        if (count($array[$end_date]['assets']) === 0) {
            $array[$end_date]['assets'][] = 'Heating Equipment';
        }
        $array[$end_date]['assets'] = array_unique($array[$end_date]['assets']);
        return $array;
    }

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
            $weekContracts = [];
            foreach ($c->contracts as $contract) {
                $temp_end = $contract->end_date;

                $is_processed_1 = $contract->is_processed_1;
                $is_processed_4 = $contract->is_processed_4;
                $is_processed_8 = $contract->is_processed_8;

                if ($temp_end >= $week1Minus2Day && $temp_end <= $week1 && $is_processed_1 === null) {
                    $weekContracts = $this->append($weekContracts, $contract, '1 wk');
                } else if ($temp_end >= $week4Minus2Day && $temp_end <= $week4 && $is_processed_4 === null) {
                    $weekContracts = $this->append($weekContracts, $contract, '4 wks');
                } else if ($temp_end >= $week8Minus2Day && $temp_end <= $week8 && $is_processed_8 === null) {
                    $weekContracts = $this->append($weekContracts, $contract, '8 wks');
                }
            }
            $c->contractsFiltered = $weekContracts;
            $i++;
        }


        return view('admin.private.private', [
            'customers' => $customers,
            'limit' => $limit,
            'week1' => new \DateTime('+1 week'),
            'week4' => new \DateTime('+4 week'),
            'week8' => new \DateTime('+8 week'),
        ]);
    }

    public function viewPdf($id, Request $request)
    {

        $customer = Customer::find($id);
        $type = $request->get('type');
        $date = $request->get('date');
        if (!$customer) {
            return redirect()->route('private.all');
        }
        $generator = new TemplateGenerator();
        if ($customer->email) {
            $templates = [
                '1 wk' => Templates::where('alias', '=', Templates::PRIVATE_1_WEEK)->first(),
                '4 wks' => Templates::where('alias', '=', Templates::PRIVATE_4_WEEK)->first(),
                '8 wks' => Templates::where('alias', '=', Templates::PRIVATE_8_WEEK)->first(),
            ];
            $html = $generator->fillPrivateEmailByCustomer($customer, $type, $date, $templates[$type]->html_body);

            return view('admin.private.email_view', [
                'htmls' => [
                    $html
                ]
            ]);
        }
        $pdf_folder = \Illuminate\Support\Facades\Config::get('constants.storage_pdf');
        $docx = $generator->fillPrivateByCustomer($customer, $type, $date);
        if (!$docx) {
            return redirect()->route('private.all');
        }
        $pdf = $generator->generatePdfFromDocx($docx);
        $customer->docx = $docx;
        $customer->pdf = $pdf;
        $customer->save();


        return response()->file($pdf_folder . '/' . $customer->pdf);
    }

    public function generate(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $data = $request->all();
        $privates = $data['private'] ?? null;
        $filled = [];
        $emails = [];
        $templates = [
            '1 wk' => Templates::where('alias', '=', Templates::PRIVATE_1_WEEK)->first(),
            '4 wks' => Templates::where('alias', '=', Templates::PRIVATE_4_WEEK)->first(),
            '8 wks' => Templates::where('alias', '=', Templates::PRIVATE_8_WEEK)->first(),
        ];
//
//
//
//
//
        $generator = new TemplateGenerator();
        $sim = new simProRequestService();
        if ($privates && is_array($privates)) {
            foreach ($privates as $private) {
                parse_str($private, $output);
//                    $otput = ['id', 'type', 'date']
                if (!array_key_exists('id', $output)
                    || !array_key_exists('type', $output)
                    || !array_key_exists('date', $output)) {
                    continue;
                }
                $type = $type_origin = $output['type'];
                if ($type == '1 wk') {
                    $type = 'is_processed_1';
                } else if ($type == '4 wks') {
                    $type = 'is_processed_4';
                } else if ($type == '8 wks') {
                    $type = 'is_processed_8';
                } else {
                    $type = null;
                }
                if (!$type) {
                    continue;
                }

                $customer = Customer::find($output['id']);
                if (!$customer) {
                    continue;
                }
                $contracts = Contract::where('customer_id', '=', $output['id'])
                    ->where('end_date', 'LIKE', $output['date'] . '%')
                    ->where($type, '=', null)
                    ->get();


                if ($customer->email && $templates[$type_origin] && $templates[$type_origin]->html_body) {
                    $html = $generator->fillPrivateEmailByCustomer($customer, $type_origin, $output['date'], $templates[$type_origin]->html_body);
//
                    $emails[] = $html;
                    Sender::send('info@dokkit.co.uk', 'Private letter', $html);
                } else {
                    $docx = $generator->fillPrivateByCustomer($customer, $type_origin, $output['date']);
                    $pdf = $generator->generatePdfFromDocx($docx);
                    $filled[] = [
                        'pdf' => $pdf,
                        'page' => '1-2'
                    ];
                }


                foreach ($contracts as $contract) {
                    $contract->{$type} = true;
                    $contract->save();
                }
            }
        }

        if (count($filled) > 0 || count($emails) > 0) {
            $merged = $generator->mergePdfs($filled);
            $log = Logs::create([
                'customer_type' => 'Private',
                'letters_generated' => count($filled),
                'email_generated' => count($emails),
                'pdf' => $merged
            ]);

            if (count($emails) > 0) {
//                base64_encode(gzcompress($letterBody, 9));
//                gzuncompress(base64_encode(gzcompress(base64_decode($this->letterBody));
                $log->emails = base64_encode(gzcompress(view('admin.private.email_view', [
                    'htmls' => $emails
                ])->render()));
                $log->save();
            }


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
