<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/27/18
 * Time: 4:46 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'n_customers';
    protected $fillable = [
        'company_name',
        'first_name',
        'last_name',
        'company_id',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
    ];

    public function getName()
    {
        return trim($this->company_name ?: ($this->first_name . ' ' . $this->last_name));
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }
}