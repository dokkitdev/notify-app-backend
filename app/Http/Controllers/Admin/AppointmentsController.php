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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $start = $request->get('start') ?? null;
        $end = $request->get('end') ?? null;
        if ($start) {
            $start = \DateTime::createFromFormat('d.m.Y', $start);
        } else {
            $start = new \DateTime();
        }
        $start->setTime(0, 0, 0);


        if ($end) {
            $end = \DateTime::createFromFormat('d.m.Y', $end);
        } else {
            $end = new \DateTime('+3 day');
        }
        $end->setTime(23, 59, 59);


        $appointments = Appointment::where('send_date', '>=', $start)
            ->where(function ($query) {
                $query->where('is_proccessed', '<>', 1)
                    ->orWhere('is_proccessed', '=', null);
            })
            ->where('send_date', '<=', $end)
            ->orderBy('send_date', 'ASC')
            ->paginate($limit);

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'today' => $start,
            'limit' => $limit,
            'start' => $start->format('d.m.Y'),
            'end' => $end->format('d.m.Y'),
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
        exec('php ' . base_path() . '/artisan upload:job > /dev/null 2>&1 &');
        return redirect()->route('appointments.all')->with([
            'ok' => 'Import jobs are being processed and will appear in the page shortly.',
        ]);
    }

    public function generate(Request $request)
    {

        $template = Templates::where('alias', '=', Templates::APPOINTMENT_LETTER)->first();
        if ($template->html === null) {
            return redirect()->back()->with([
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

                $log = Logs::create([
                    'customer_type' => 'Appointment',
                    'letters_generated' => count($filled),
                    'email_generated' => 0,
                ]);
                $filled = implode(',', $filled);
                $command = 'php ' . base_path() . '/artisan combine:pdf ' . $filled . ' ' . $log->id . '  > /dev/null 2>&1 &';
                $log->command = $command;
                $log->save();
                exec('php ' . base_path() . '/artisan queue:log:start > /dev/null 2>&1 &');
                return redirect()->back()->with([
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

            return redirect()->back()->with([
                'ok' => 'Appointment Letters has been successfull generated.',
                'merged' => $merged,
            ]);
        }

        return redirect()->back()->with([
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

    public function clearDublicates()
    {
        $result = DB::select('SELECT job_id, min(`send_date`) as mtime FROM appointments GROUP BY job_id HAVING COUNT(*) > 1');
        foreach ($result as $r) {

            $appointment = Appointment::where('job_id', '=', $r->job_id)
                ->where('is_proccessed', '=', 1)
                ->orderBy('send_date', 'ASC')
                ->first();
            if ($appointment) {
                $id = $appointment->id;
            } else {
                $finded = DB::select('SELECT id FROM appointments WHERE job_id = :job and `send_date` = :send_date LIMIT 1', [
                    $r->job_id,
                    $r->mtime
                ]);
                $id = $finded[0]->id;
            }
            DB::delete('DELETE FROM `appointments` WHERE job_id = :job AND id <> :id;', [
                $r->job_id,
                $id
            ]);
        }
    }
}
