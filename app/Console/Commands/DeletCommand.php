<?php

namespace App\Console\Commands;

use App\Service\simProRequestService;
use Illuminate\Console\Command;

class DeletCommand extends Command
{
    protected $signature = 'delete:command';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $sim = new simProRequestService();

        $array = [
            '900',
            '892',
            '870',
            '868',
            '837',
            '829',
            '819',
            '808',
            '805',
            '804',
            '801',
            '797',
            '788',
            '779',
            '778',
            '777',
            '772',
            '757',
            '748',
            '736',
            '733',
            '725',
            '704',
            '690',
            '682',
            '681',
            '675',
            '672',
            '671',
            '670',
            '661',
            '654',
            '651',
            '650',
            '648',
            '646',
            '641',
            '638',
            '637',
            '631',
            '627',
            '623',
            '618',
            '616',
            '615',
            '612',
            '610',
            '608',
            '606',
            '602',
            '597',
            '580',
            '573',
            '572',
            '561',
            '559',
            '557',
            '555',
            '552',
            '540',
            '537',
            '535',
            '530',
            '520',
            '515',
            '512',
            '510',
            '507',
            '501',
            '482',
            '478',
            '477',
            '460',
            '456',
            '453',
            '451',
            '442',
            '437',
            '431',
            '424',
            '422',
            '413',
            '410',
            '399',
            '373',
            '367',
            '359',
            '358',
            '353',
            '346',
            '345',
            '338',
            '330',
            '323',
            '312',
            '309',
            '288',
            '284',
            '262',
        ];
        foreach ($array as $a) {
            $companies = $sim->getRequest('GET', '/api/v1.0/companies/0/jobs/' . $a . '/attachments/files/');
            if (count($companies) > 0) {
                $sim->deleteRequest('/api/v1.0/companies/0/jobs/' . $a . '/attachments/files/' . $companies[0]->ID);
            }
        }

    }
}
