<?php

if (!function_exists('gettimeunit_helper')) {
    function gettimeunit_helper($timeInSeconds, $default = false) {
        if(empty($timeInSeconds) || $timeInSeconds == 0 || $timeInSeconds == '00:00'){
            return $default;
        }
        return gmdate('H:i', $timeInSeconds);
    }
}


if (!function_exists('getdate_helper')) {
    function getdate_helper($type = 'date', $epoch, $default = false) {
        if(empty($epoch) || $epoch == 0){
            return $default;
        }

        if($type == 'date'){
            return gmdate('Y-m-d', $epoch);
        }elseif($type == 'time'){
            return gmdate('H:i', $epoch);
        }elseif($type == 'date_time' || $type == 'timestamp'){
            return gmdate('Y-m-d H:i:s', $epoch);
        }else{
            return gmdate('Y-m-d', $epoch);
        }
    }
}
