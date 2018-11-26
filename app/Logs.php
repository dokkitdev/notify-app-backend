<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Logs extends Model
{
    protected $table = 'logs';
    protected $fillable = ['customer_type', 'letters_generated', 'email_generated', 'pdf'];

    public function getCreatedAt()
    {
        $date = $this->created_at;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        return $date ? $date->format('Y-m-d H:i') : '';
    }
}
