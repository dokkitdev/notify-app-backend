<?php

namespace App\Http\Controllers\Admin;


use App\Models\Appointment;
use App\Models\AssetReportValidation;
use App\Models\HousingJob;
use App\Models\PrivateCustomer;

use function view;

class DashboardController extends \Illuminate\Routing\Controller
{
    public function getDashboard()
    {
        $chl = Appointment::query()
            ->where('type', Appointment::CHL_TYPE)
            ->where(function ($query) {
                $query->where('is_proccessed', '<>', 1)
                    ->orWhere('is_proccessed', '=', null);
            })
            ->where('send_date', '>', (new \DateTime('+4 day'))->format('Y-m-d'))
            ->count();

        $appointments = Appointment::query()
            ->where(function ($query) {
                $query->where('is_proccessed', '<>', 1)
                    ->orWhere('is_proccessed', '=', null);
            })
            ->where('type', Appointment::NORMAL_TYPE)
            ->where('send_date', '>', (new \DateTime('+4 day'))->format('Y-m-d'))
            ->count();

        $private_debit = PrivateCustomer::query()
            ->where('is_processed', false)
            ->where('type', PrivateCustomer::DEBIT)
            ->count();

        $private_annual = PrivateCustomer::query()
            ->where('is_processed', false)
            ->where('type', PrivateCustomer::ANNUAL)
            ->count();

        $assets_validation = AssetReportValidation::query()->count();


        return view(
            'admin.dashboard',
            compact(
                'chl',
                'appointments',
                'private_debit',
                'private_annual',
                'assets_validation'
            )
        );
    }
}
