<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentLogged extends Model
{
    protected $table = 'appointments_logged';
    protected $fillable = [
        'job_id',
        'send_date',
        'site_id',
        'letter_type'
    ];

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
        $t1700 = strtotime('17:00');
        if ($scheduleTime < $t1200) {
            $time = 'between 8AM and 12PM';
        } else if ($t1200 <= $scheduleTime && $scheduleTime < $t1700) {
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
