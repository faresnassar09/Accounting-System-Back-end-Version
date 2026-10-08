<?php

use Carbon\Carbon;



if (!function_exists('get_start_of_year')) {
    function get_start_of_year($year){
        $dateStr = (is_numeric($year) && strlen((string)$year) === 4) ? "{$year}-01-01" : $year;
        return Carbon::parse($dateStr)->startOfYear()->format('Y-m-d');        
    }
}

if (!function_exists('get_end_of_year')){
    function get_end_of_year($year){
        $dateStr = (is_numeric($year) && strlen((string)$year) === 4) ? "{$year}-12-31" : $year;
        return Carbon::parse($dateStr)->endOfYear()->format('Y-m-d');
    }

    if (!function_exists('get_start_of_next_financial_year')) {
        function get_start_of_next_financial_year($year,$number = 1){
            $dateStr = (is_numeric($year) && strlen((string)$year) === 4) ? "{$year}-01-01" : $year;
            return Carbon::parse($dateStr)
                ->addYears($number)
                ->startOfYear()
                ->format('Y-m-d');
        }
    }
}