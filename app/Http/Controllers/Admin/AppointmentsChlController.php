<?php


namespace App\Http\Controllers\Admin;

use App\Appointment;
use App\Console\Commands\ProcessAppointmentChlCommand;
use App\Console\Commands\ProcessPrivateCommand;
use App\Http\Controllers\Controller;
use App\Logs;
use App\Models\AppointmentLogged;
use App\Service\AppointmentChlService;
use App\Service\Exceptions\MessageException;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentsChlController extends Controller
{

    public function index(Request $request)
    {
        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'send_date';
        $direction = $request->get('direction') ?: 'asc';

        $appointments = Appointment::where('type', Appointment::CHL_TYPE)
            ->where(function ($query) {
                $query->where('is_proccessed', '<>', 1)
                    ->orWhere('is_proccessed', '=', null);
            })
            ->where('send_date', '>', (new \DateTime('+4 day'))->format('Y-m-d'))
            ->orderBy($sort, $direction)
            ->paginate($limit);

        $today = new \DateTime();
        return view('admin.appointments.index_chl', [
            'appointments' => $appointments,
            'limit' => $limit,
            'start' => $today->format('d.m.Y'),
            'end' => $today->format('d.m.Y'),
        ]);
    }

    public function viewPdf($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return redirect()->route('chl.appointments.all');
        }

        try {
            AppointmentChlService::generateFilesForAppointment($appointment);
            $pdf = $appointment->pdf;
            $pdfFolder = Config::get('constants.storage_pdf');
            return response()->file($pdfFolder . '/' . $pdf);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('chl.appointments.all')->with([
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function generate(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');
        $appointments = $request->request->get('appointments');
        try {
            if (!$appointments || !is_array($appointments) || !count($appointments)) {
                throw new MessageException([
                    'error' => 'Please select at least one private letter.',
                ]);
            }
            $countAppointment = count($appointments);
            $log = Logs::create([
                'customer_type' => 'CHL letter',
                'letters_generated' => 0,
                'email_generated' => 0,
            ]);
            if ($countAppointment > 15) {
                foreach ($appointments as $appointment) {
                    $appointment = Appointment::find($appointment);
                    if (!$appointment) {
                        continue;
                    }
                    $appointment->is_proccessed = 1;
                    $appointment->save();
                }
                $log->command = ProcessAppointmentChlCommand::getCommand($appointments, $log);
                $log->save();
                throw new MessageException([
                    'ok' => 'Your letters are being processed and will appear in the logs page shortly.',
                ]);
            }
            AppointmentChlService::startProcessing($appointments, $log);
            $message = [
                'ok' => 'Appointment Letters has been successfully generated.'
            ];
        } catch (MessageException $exception) {
            $message = $exception->getDescription();
        }
        return redirect()->back()->with($message);
    }
}