<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-20
 * Time: 07:48
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PrivateCustomer extends Model
{
    protected $table = 'new_private_customers';
    protected $fillable = [
        'recurring_type',
        'recurring_invoice_id',
        'next_recurring_date',
        'customer_id',
        'site_name',
        'company_name',
        'customer_title',
        'customer_given_name',
        'customer_family_name',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_postal_code',
        'site_address',
        'site_city',
        'site_state',
        'site_postal_code',
        'is_company',
        'period',
        'direct_date',
        'payer_reference',
        'payer_account_name',
        'type',
        'is_processed',
        'docx',
        'pdf',
        'direct_month',
    ];
    const ANNUAL = 'Annual payment';
    const DEBIT = 'Direct Debit';

    const PROJECT = 'Project';
    const SERVICE = 'Service';

    public function getName()
    {
        if ($this->company_name) {
            return $this->company_name;
        } else if (trim($this->customer_title . ' ' . $this->customer_given_name . ' ' . $this->customer_family_name)) {
            return trim($this->customer_title . ' ' . $this->customer_given_name . ' ' . $this->customer_family_name);
        }
        return 'The Occupier';
    }

    public function getPeriod()
    {
        return $this->period ?: '12 months';
    }

    public function getPeriodInteger()
    {
        return preg_replace(
            "/[^0-9]/",
            '',
            $this->getPeriod()
        );
    }

    public function getDirectDate()
    {
        $nextDate = \DateTime::createFromFormat('Y-m-d', $this->next_recurring_date);
        $directDate = $this->direct_date ?: '1st of';
        if ($this->direct_month) {
            $dateTimeByMonth = \DateTime::createFromFormat('!F', $this->direct_month);
            if ((int)$dateTimeByMonth->format('m') < (int)$nextDate->format('m')) {
                $nextDate->modify('+1 year');
            }
            return $this->direct_date . ' ' . $this->direct_month . ' ' . $nextDate->format('Y');
        }

        $nextDate = \DateTime::createFromFormat('Y-m-d', $this->next_recurring_date)->modify('+1 month');
        $month = $nextDate->format('F');
        $year = $nextDate->format('Y');
        return $directDate . ' ' . $month . ' ' . $year;
    }


    public function costCenters()
    {
        return $this->hasMany(PrivateCostCenter::class);
    }

    public function assets()
    {
        return $this->hasMany(PrivateAsset::class)->orderBy('type');
    }
}
