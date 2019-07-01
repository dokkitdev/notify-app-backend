<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentSaved extends Model
{
    protected $table = 'appointments_saved';
    protected $fillable = [
        'job_id',
        'schedule_date'
    ];
}
