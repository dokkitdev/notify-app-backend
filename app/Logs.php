<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'logs';
    protected $fillable = ['customer_type', 'letters_generated', 'email_generated', 'pdf'];


}
