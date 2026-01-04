<?php

namespace App\Services\Logging;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoggerService {


    public function successLogger($message = '' , $data = []){

        $data = $data+ [
            'userId' => Auth::id(),
            'tenant_id' => tenancy()->tenant->id,
            'tenant_domain' => tenancy()->tenant->domain,
        ];

        Log::channel('accounting')->info($message , $data);

    }

    public function failedLogger($message = '' , $data = [] ,  $errorMessage = null){

        $data = $data+ [
            'userId' => Auth::id(),
            'tenant_id' => tenancy()->tenant->id,
            'tenant_domain' => tenancy()->tenant->domain,
        ];

        Log::channel('accounting')->error($message, [$data,$errorMessage] );
    }


}