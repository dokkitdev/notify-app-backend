<?php


namespace App\Http\Controllers\Admin;

use App\Console\Commands\ProcessAppointmentChlCommand;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Logs;
use App\Models\Templates;
use App\Service\AppointmentChlService;
use App\Service\Exceptions\MessageException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

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

        foreach ($appointments as $appointment) {
            switch ($appointment->letter_type) {
                case Templates::APPOINTMENT_LETTER_CHL_1:
                    $appointment->letter_type_name = 'O 1';
                    break;
                case Templates::APPOINTMENT_LETTER_CHL_2:
                    $appointment->letter_type_name = 'O 2';
                    break;
                case Templates::APPOINTMENT_LETTER_CHL_3:
                    $appointment->letter_type_name = 'O 3';
                    break;
                case Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_1:
                case Templates::APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_1:
                    $appointment->letter_type_name = 'E 1';
                    break;
                case Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_2:
                case Templates::APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_2:
                    $appointment->letter_type_name = 'E 2';
                    break;
                case Templates::APPOINTMENT_LETTER_ELECTRIC_CHL_3:
                case Templates::APPOINTMENT_REMEDIAL_WORK_LETTER_CHL_3:
                    $appointment->letter_type_name = 'E 3';
                    break;
                case Templates::APPOINTMENT_LETTER_GAS_CHL_1:
                    $appointment->letter_type_name = 'G 1';
                    break;
                case Templates::APPOINTMENT_LETTER_GAS_CHL_2:
                    $appointment->letter_type_name = 'G 2';
                    break;
                case Templates::APPOINTMENT_LETTER_GAS_CHL_3:
                    $appointment->letter_type_name = 'G 3';
                    break;
            }
        }


        $appointments->appends($request->except(['page', '_token']));


        return view('admin.appointments.index_chl', [
            'appointments' => $appointments,
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

            return response()->file($pdfFolder.'/'.$pdf);
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
            if ($countAppointment >= 1) {
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
                'ok' => 'Appointment Letters has been successfully generated.',
            ];
        } catch (MessageException $exception) {
            $message = $exception->getDescription();
        }

        return redirect()->back()->with($message);
    }
}
