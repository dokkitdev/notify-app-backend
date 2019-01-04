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
        'title',
        'first_name',
        'last_name',
        'company_id',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'email',
        'is_company',
        'pdf',
        'docx',
    ];

    public function getName()
    {
        if ($this->company_name) {
            return $this->company_name;
        } else if (trim($this->title . ' ' . $this->first_name . ' ' . $this->last_name)) {
            return trim($this->title . ' ' . $this->first_name . ' ' . $this->last_name);
        }
        return 'The Occupier';
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