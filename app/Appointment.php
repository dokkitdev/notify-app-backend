<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';
    protected $fillable = [
        'title',
        'family_name',
        'given_name',
        'address',
        'state',
        'city',
        'country',
        'postcode',
        'job_id',
        'send_date',
        'work_type',
        'appointment_id',
        'site_id',
        'time',
        'pdf',
        'docx',
        'is_proccessed'
    ];

    public function getContact()
    {

        if ($this->title && strlen($this->title) > 0) {
            return $this->title . ' ' . substr($this->given_name, 0, 1) . '. ' . $this->family_name;
        }
        return $this->given_name . ' ' . $this->family_name;
    }

    public function getFormatedScheduleDate()
    {
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        return $date ? $date->format('l d/m/Y') : '';
    }

    public function getFormatedScheduleDateWithoutDay()
    {
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        return $date ? $date->format('d/m/Y') : '';
    }

    public function getFormattedWithWeekday()
    {
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        return $date ? $date->format('d/m/Y') : '';
    }

    public function getFormatedScheduleTime()
    {
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        if (!$date) {
            return '';
        }
        $scheduleTime = strtotime($date->format('H:i'));
        $t1200 = strtotime('12:00');
        $t1201 = strtotime('12:01');
        $t1700 = strtotime('17:00');
        if ($scheduleTime < $t1200) {
            $time = 'between 8AM and 12PM';
        } else if (true) {
            $time = 'between 12PM and 5PM';
        } else {
            $time = 'between 5PM and 8PM';
        }
        return $time;
    }



    public function getDaysToScheduleDate($today = null)
    {
        $today = $today ?: new \DateTime();
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        if (!$date) {
            return '';
        }
        $daysToSchedule = $today->diff($date)->d;
        return $daysToSchedule;
    }
}