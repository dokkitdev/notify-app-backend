<?php
/**
 * Created by PhpStorm.
 * User: mevius
 * Date: 2019-05-22
 * Time: 12:22
 */

namespace App\Helpers;


class Date
{
    public static function getFormattedToday()
    {
        $today = new \DateTime();
        return self::prettyFormatting($today);
    }


    public static function prettyFormatting($date)
    {
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
        $date = $day . ' ' . $month . ' ' . $year;
        return $date;
    }
}