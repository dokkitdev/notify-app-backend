<?php

namespace App\Http\Controllers\Admin;

use App\Appointment;
use App\Http\Requests\UserRequest;
use App\Mail\AdminRegister;
use App\Service\TemplateGenerator;
use App\Template;
use App\Templates;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PDFMerger\PDFMerger;

class AppointmentsController extends Controller
{
    public function index()
    {
        $today = new \DateTime('');
        $today->setTime(0, 0, 0);
        $fourDay = new \DateTime('+4 day');
        $fourDay->setTime(23, 59, 59);

        $appointments = Appointment::where('send_date', '>=', $today)
//            ->where('send_date', '<=', $fourDay)
            ->get();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
            'today' => $today,
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
        if ($appointments && is_array($appointments)) {
            foreach ($appointments as $a) {
                $appointment = Appointment::find($a);
                /** Проверка на существование или сгенерированость */
                if (!$appointment) {
                    unset($appointment);
                    continue;
                }
                if ($appointment->pdf === null || $appointment->docx === null) {
                    $docx = $generator->fillAppoinmentLetterFromDocxTemplate($template->docx, $appointment);
                    $pdf = $generator->generatePdfFromDocx($docx);
                    $appointment->docx = $docx;
                    $appointment->pdf = $pdf;
                    $appointment->save();
                }

                $filled[] = $appointment->pdf;
            }
        }
        if (count($filled) > 0) {
            $merged = $generator->mergePdfs($filled);

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
