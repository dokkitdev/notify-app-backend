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
        'is_proccessed',
        'customer_id'
    ];

    public function getAddress()
    {
        $str = $this->address;
        $str = str_replace("\r", '', $str);
        $str = str_replace("\n", ', ', $str);
        $str = preg_replace('/\s*,\s*/', ', ', $str);
        return $str;
    }


    public function getContact()
    {

        if ($this->title && strlen($this->title) > 0) {
            return $this->title . ' ' . substr($this->given_name, 0, 1) . (strlen($this->given_name) > 0 ? '. ' : '') . $this->family_name;
        }
        return $this->given_name . ' ' . $this->family_name;
    }

    public function getFormatedScheduleDate()
    {
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
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

    public function getFormatedScheduleDateWithoutDay()
    {
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;
        return $date ? $date->format('d/m/Y') : '';
    }

    public function getYmd()
    {
        $date = $this->send_date;
        $date = $date ? \DateTime::createFromFormat('Y-m-d H:i:s', $date) : null;

        return $date ? $date->format('Y-m-d') : '';
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
        if ($scheduleTime <= $t1200) {
            $time = 'between 8AM and 12PM';
        } else if ($t1201 < $scheduleTime && $scheduleTime < $t1700) {
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