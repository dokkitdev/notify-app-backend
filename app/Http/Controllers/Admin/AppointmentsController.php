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

class AppointmentsController extends Controller
{
    public function index()
    {
        $today = new \DateTime('');
        $today->setTime(0, 0, 0);
        $fourDay = new \DateTime('+4 day');
        $fourDay->setTime(23, 59, 59);

        $appointments = Appointment::where('send_date', '>=', $today)
            ->where('send_date', '<=', $fourDay)
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
        $filled = 0;
        if ($appointments && is_array($appointments)) {
            $generator = new TemplateGenerator();
            foreach ($appointments as $a) {
                $appointment = Appointment::find($a);
                /** Проверка на существование или сгенерированость */
                if (!$appointment ||
                    ($appointment && $appointment->pdf !== null && $appointment->docx !== null)
                ) {
                    unset($appointment);
                    continue;
                }
                $docx = $generator->fillAppoinmentLetterFromDocxTemplate($template->docx, $appointment);
                $pdf = $generator->generatePdfFromDocx($docx);
                $appointment->docx = $docx;
                $appointment->pdf = $pdf;
                $appointment->save();
                ++$filled;
            }
        }
        $alert = $filled > 0
            ? ['ok' => 'Appointment Letters has been successfull generated']
            : ['error' => 'Please select at least one appointment letter'];
        return redirect()->route('appointments.all')->with($alert);
    }
}
