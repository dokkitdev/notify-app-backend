<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 12/2/18
 * Time: 1:14 PM
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class HousingJob extends Model
{
    protected $table = 'n_housing_job';
    protected $fillable = [
        'job_id',
        'company_name',
        'due_date',
        'tags',
        'stage',
        'job_name',
        'site_id',
        'address',
        'city',
        'state',
        'postal_code',
        'given_name',
        'family_name',
        'is_proccessed',
        'cell_phone',
        'work_phone',
        'email',
        'schedule_date'
    ];


    private function initDueDate()
    {
        $date = $this->due_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        return $date;
    }

    private function initScheduleDate()
    {
        $date = $this->schedule_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        return $date;
    }

    public function getDueDate()
    {
        $dueDate = $this->initDueDate();
        return $dueDate ? $dueDate->format('Y-m-d') : '';
    }

    public function getScheduleDate()
    {
        $date = $this->schedule_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;

        return $date ? $date->format('Y-m-d') : '';
    }

    public function getScheduleDateWithDay()
    {
        $dueDate = $this->initScheduleDate();
        if ($dueDate == '') {
            return '';
        }
        $weekday = $dueDate->format('l');
        $month = $dueDate->format('F');
        $year = $dueDate->format('Y');
        $day = ltrim($dueDate->format('d'), '0');
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

    public function siteContact()
    {
        return trim($this->given_name . ' ' . $this->family_name) ?: 'The Occupier';
    }

    public function getDueDateWithDay()
    {
        $dueDate = $this->initDueDate();
        if ($dueDate == '') {
            return '';
        }
        $weekday = $dueDate->format('l');
        $month = $dueDate->format('F');
        $year = $dueDate->format('Y');
        $day = ltrim($dueDate->format('d'), '0');
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

    public function getContactPhone()
    {
        if ($this->work_phone) {
            return $this->work_phone;
        }
        if ($this->cell_phone) {
            return $this->cell_phone;
        }
        return 'Not Listed';
    }

}