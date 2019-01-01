<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 11/27/18
 * Time: 4:46 PM
 */

namespace App\Models;


use App\Service\Sender\PrivateSender;
use App\Templates;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $table = 'n_contracts';
    protected $fillable = [
        'contract_id',
        'name',
        'contract_no',
        'end_date',
        'value',
        'is_processed_1',
        'is_processed_4',
        'is_processed_8',
    ];

    public function getEndDate()
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->end_date);
        return $date ?: '';
    }

    public function getEndDateYmd()
    {
        $date = $this->getEndDate();
        return $date ? $date->format('Y-m-d') : '';
    }

    public function getExpireDate()
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->end_date);
        if (!$date) {
            return '';
        }
        $weekday = $date->format('l');
        $month = $date->format('F');
        $year = $date->format('Y');
        $day = ltrim($date->format('d'), '0');
        if ($day % 10 == 1 && $day != 11) {
            $day .= 'st';
        } else if ($day % 10 == 2 && $day != 12) {
            $day .= 'nd';
        } else if ($day % 10 == 3 && $day != 13) {
            $day .= 'rd';
        } else {
            $day .= 'th';
        }

        return $weekday . ', ' . $day . ' ' . $month . ' ' . $year;
    }

    public function setProcess()
    {
        $week1 = new \DateTime('+1 week');
        $week4 = new \DateTime('+4 week');
        $week8 = new \DateTime('+8 week');
        $end_date = $this->getEndDate();
        $end_date = $end_date->format('Y-m-d');
        if ($week1->format('Y-m-d') == $end_date) {
            $this->is_processed_1 = true;
            PrivateSender::generateAndSend($this, Templates::PRIVATE_1_WEEK);
        } else if ($week4->format('Y-m-d') == $end_date) {
            $this->is_processed_4 = true;
            PrivateSender::generateAndSend($this, Templates::PRIVATE_4_WEEK);
        } else if ($week8->format('Y-m-d') == $end_date) {
            $this->is_processed_8 = true;
            PrivateSender::generateAndSend($this, Templates::PRIVATE_8_WEEK);
        }
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}