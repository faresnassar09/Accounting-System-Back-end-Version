<?php

use Carbon\Carbon;



if (!function_exists('get_start_of_year')) {
    
    
    function get_start_of_year($year){

       return Carbon::createFromFormat('Y',$year)->startOfYear()->format('Y-m-d');

    }
}

if (!function_exists('get_end_of_year')){

    function get_end_of_year($year){

        return Carbon::createFromFormat('Y',$year)->endOfYear()->format('Y-m-d');
  
    }

    if (!function_exists('get_start_of_next_financial_year')) {
        
        function get_start_of_next_financial_year($year,$number = 1){
        return Carbon::createFromFormat('Y',$year)
        ->addYears($number)
        ->startOfYear()
        ->format('Y-m-d');

        }
    }

  
}