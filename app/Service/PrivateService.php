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
        if ($countCustomer > 15) {
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

        $file = self::process($customers, $log);
        return [
            'ok' => 'Private Letters has been successfull generated.',
            'merged' => $file,
        ];
    }

    public static function process($customers, $log)
    {
	dump($customers);
        $pdfs = [];
        foreach ($customers as $customer) {
            $customer = PrivateCustomer::find($customer);
            if (!$customer) {
                continue;
            }
	  dump('------- START ------');
            self::generateFilesForCustomer($customer);
            self::setIsProcessed($customer);
	   dump('------ END   ------');
	   dump($customer->pdf);
            $pdfs[] = $customer->pdf;
        }
        $today = new \DateTime();
        $file = 'Privates.' . $today->format('Y-m-d.H-i-s') . '.pdf';
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
        $sim->uploadNewPrivate($customer, $pdf);
        $customer->docx = $docx;
        $customer->pdf = $pdf;
        $customer->save();
    }


}
