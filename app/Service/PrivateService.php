<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-24
 * Time: 09:10
 */

namespace App\Service;


use App\Console\Commands\ProcessPrivateCommand;
use App\Logs;
use App\Models\PrivateCustomer;
use App\Service\Exceptions\MessageException;
use Illuminate\Support\Facades\Config;

class PrivateService
{
    public static function startProcessing($customers)
    {
        if (!$customers || !is_array($customers) || !count($customers)) {
            throw new MessageException([
                'error' => 'Please select at least one private letter.',
            ]);
        }

        $countCustomer = count($customers);
        $log = Logs::create([
            'customer_type' => 'Private',
            'letters_generated' => 0,
            'email_generated' => 0,
        ]);
        foreach ($customers as $customer) {
            $customer = PrivateCustomer::find($customer);
            if (!$customer) {
                continue;
            }
            self::setIsProcessed($customer);
        }
        $log->command = ProcessPrivateCommand::getCommand($customers, $log);
        $log->save();
        throw new MessageException([
            'ok' => 'Your letters are being processed and will appear in the logs page shortly.',
        ]);
    }

    public static function process($customers, $log)
    {
        $pdfFolder = Config::get('constants.storage_pdf') . '/';

        $pdfs = [];
        $isSuccess = true;
        foreach ($customers as $customer) {
            $customer = PrivateCustomer::find($customer);
            if (!$customer) {
                continue;
            }
            dump('------- START ------');
            if (!$customer->is_processed || !$customer->pdf) {
                self::generateFilesForCustomer($customer);
                self::setIsProcessed($customer);
            }
            dump('------ END   ------');
            $pdf = $customer->pdf;
            if (!file_exists($pdfFolder . $pdf)) {
                $customer->pdf = null;
                $customer->save();
                $isSuccess = false;
            }
            $pdfs[] = $customer->pdf;
        }
        if (!$isSuccess) {
            return false;
        }
        $today = new \DateTime();
        $file = 'Privates.' . $today->format('Y-m-d.H-i-s') . '.pdf';
        dump($pdfs);
        TemplateGenerator::mergeAllPdfsPages($pdfs, $file);
        $log->letters_generated = count($pdfs);
        $log->email_generated = 0;
        $log->pdf = $file;
        $log->save();
        return $file;
    }

    public static function setIsProcessed($customer)
    {
        if (!$customer->type == PrivateCustomer::ANNUAL) {
            $customer->is_processed = 1;
            $customer->save();
        } else {
            $annualCustomers = PrivateCustomer::where('customer_id', $customer->customer_id)
                ->where('next_recurring_date', $customer->next_recurring_date)
                ->get();
            foreach ($annualCustomers as $annualCustomer) {
                $annualCustomer->is_processed = 1;
                $annualCustomer->save();
            }
        }
    }


    static function generateFilesForCustomer($customer)
    {
        $generator = new PrivateTemplateGenerator();
        $docx = $generator->generateDocx($customer);
        $pdf = TemplateGenerator::sGeneratePdfFromDocx($docx);
        $sim = new simProRequestService();
//        $sim->uploadNewPrivate($customer, $pdf);
        $customer->docx = $docx;
        $customer->pdf = $pdf;
        $customer->save();
    }


}
