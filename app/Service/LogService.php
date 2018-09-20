<?php

namespace App\Service;


use App\Letter;
use Carbon\Carbon;

class LogService
{
    public function getStandardReport($depth = 30)
    {
        $result = [];
        for($date = Carbon::now()->addDays(-$depth); $date->lte(Carbon::now()); $date->addDay()) {
            $curDate = $date->format('Y-m-d');
            $result[] = [
                'date' => $curDate,
                'customer_type' => 'Private',
                'letters_generated' => Letter::whereBetween('generated_at', [$curDate . ' 00:00:00', $curDate . ' 23:59:59'])->where('template_id', '>', 0)->where('letter', '>', 0)->count(),
                'emails_generated' => Letter::whereBetween('generated_at', [$curDate . ' 00:00:00', $curDate . ' 23:59:59'])->where('template_id', '>', 0)->where('email', '>', 0)->count()
            ];
            $result[] = [
                'date' => $date->format('Y-m-d'),
                'customer_type' => 'Housing',
                'letters_generated' => Letter::whereBetween('generated_at', [$curDate . ' 00:00:00', $curDate . ' 23:59:59'])->where('housing_template_id', '>', 0)->where('letter', '>', 0)->count(),
                'emails_generated' => Letter::whereBetween('generated_at', [$curDate . ' 00:00:00', $curDate . ' 23:59:59'])->where('housing_template_id', '>', 0)->where('email', '>', 0)->count()
            ];
        }
        return $result;
    }
}