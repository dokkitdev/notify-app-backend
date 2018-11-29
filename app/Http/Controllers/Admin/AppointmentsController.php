<?php

namespace App\Http\Controllers\Admin;

use App\Appointment;
use App\Http\Requests\UserRequest;
use App\Logs;
use App\Mail\AdminRegister;
use App\Service\simProRequestService;
use App\Service\TemplateGenerator;
use App\Template;
use App\Templates;
use App\User;
use Composer\Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $today = new \DateTime('');
        $today->setTime(0, 0, 0);
        $fourDay = new \DateTime('+3 day');
        $fourDay->setTime(23, 59, 59);
        $limit = $request->get('limit') ?? 20;

        $appointments = Appointment::where('send_date', '>=', $today)
            ->where(function ($query) {
                $query->where('is_proccessed', '<>', 1)
                    ->orWhere('is_proccessed', '=', null);
            })
//            ->where('send_date', '<=', $fourDay)
            ->paginate($limit);

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'today' => $today,
            'limit' => $limit,
        ]);
    }


    public function viewPdf($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return redirect()->route('appointments.all');
        }
        $template = Templates::where('alias', '=', Templates::APPOINTMENT_LETTER)->first();
        $generator = new TemplateGenerator();
        $docx = $generator->fillAppoinmentLetterFromDocxTemplate($template->docx, $appointment);
        $pdf = $generator->generatePdfFromDocx($docx);
        $appointment->docx = $docx;
        $appointment->pdf = $pdf;
        $appointment->save();

        $pdf_folder = \Illuminate\Support\Facades\Config::get('constants.storage_pdf');

        return response()->file($pdf_folder . '/' . $appointment->pdf);
    }

    public function import()
    {
//        exec('php ' . base_path() . '/artisan upload:job');
        dump('php ' . base_path() . '/artisan upload:job');die;
        return redirect()->route('appointments.all')->with([
            'ok' => 'Import jobs are being processed and will appear in the page shortly.',
        ]);
    }

    public function generate(Request $request)
    {
        $template = Templates::where('alias', '=', Templates::APPOINTMENT_LETTER)->first();
        if ($template->html === null) {
            return redirect()->route('appointments.all')->with([
                'error' => 'Please fill "Appointment letter" template by docx',
            ]);
        }

        $data = $request->all();
        $appointments = $data['appointments'] ?? null;
        $filled = [];
        $generator = new TemplateGenerator();
        $sim = new simProRequestService();
        if ($appointments && is_array($appointments)) {
            if (count($appointments) > 15) {
                foreach ($appointments as $a) {
                    $appointment = Appointment::find($a);
                    $appointment->is_proccessed = true;
                    $appointment->save();
                    $filled[] = $appointment->id;
                }
                $filled = implode(',', $filled);
                exec('php ' . base_path() . '/artisan combine:pdf ' . $filled . ' > /dev/null 2>&1 &');
                return redirect()->route('appointments.all')->with([
                    'ok' => 'Your letters are being processed and will appear in the logs page shortly.',
                ]);
            } else {
                foreach ($appointments as $a) {
                    $appointment = Appointment::find($a);
                    /** Проверка на существование или сгенерированость */
                    if (!$appointment) {
                        unset($appointment);
                        continue;
                    }
                    if (!$appointment->is_proccessed) {
                        $docx = $generator->fillAppoinmentLetterFromDocxTemplate($template->docx, $appointment);
                        $pdf = $generator->generatePdfFromDocx($docx);
                        $appointment->docx = $docx;
                        $appointment->pdf = $pdf;
//                        $sim->uploadAppointment($appointment);
                    }
                    $appointment->is_proccessed = true;
                    $appointment->save();

                    $filled[] = $appointment->pdf;
                }
            }
        }
        if (count($filled) > 0) {
            $merged = $generator->mergePdfs($filled);
            Logs::create([
                'customer_type' => 'Appointment',
                'letters_generated' => count($filled),
                'email_generated' => 0,
                'pdf' => $merged
            ]);

            return redirect()->route('appointments.all')->with([
                'ok' => 'Appointment Letters has been successfull generated.',
                'merged' => $merged,
            ]);
        }

        return redirect()->route('appointments.all')->with([
            'error' => 'Please select at least one appointment letter.',
        ]);
    }

    public function clear($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return redirect()->route('appointments.all')->with([
                'error' => 'Appointment Letter is not found',
            ]);
        }
        $appointment->docx = null;
        $appointment->pdf = null;
        $appointment->save();
        return redirect()->route('appointments.all')->with([
            'ok' => 'Appointment Letter has been successfull cleared',
        ]);
    }
}
