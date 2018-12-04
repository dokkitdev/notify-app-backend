<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentProcessed extends Model
{
    protected $table = 'appointments_processed';
    protected $fillable = [
       'job_id'
    ];
}
