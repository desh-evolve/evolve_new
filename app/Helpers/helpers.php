<?php

if (!function_exists('gettimeunit_helper')) {
    function gettimeunit_helper($timeInSeconds, $default = false) {
        if (empty($timeInSeconds) || $timeInSeconds == 0 || $timeInSeconds == '00:00') {
            return $default;
        }

        // Ensure it's a number
        $seconds = (int) $timeInSeconds;

        // Convert to hh:mm format
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }
}


if (!function_exists('getdate_helper')) {
    function getdate_helper($type = 'date', $epoch, $default = false) {
        if(empty($epoch) || $epoch == 0){
            return $default;
        }

        if($type == 'date'){
            return date('Y-m-d', $epoch);
        }elseif($type == 'time'){
            return date('H:i', $epoch);
        }elseif($type == 'date_time' || $type == 'timestamp'){
            return date('Y-m-d H:i:s', $epoch);
        }else{
            return date('Y-m-d', $epoch);
        }
    }
}
