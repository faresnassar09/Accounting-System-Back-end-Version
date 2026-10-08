<?php

namespace App\Services\Logging;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoggerService {


    public function successLogger($message = '' , $data = []){

        $tenant = tenancy()->tenant;
        $tenantDomain = $tenant?->domain ?? $tenant?->domains?->first()?->domain;

        $data = $data+ [
            'userId' => Auth::id(),
            'tenant_id' => $tenant?->id,
            'tenant_domain' => $tenantDomain,
        ];

        Log::channel('accounting')->info($message , $data);

    }

    public function failedLogger($message = '' , $data = [] ,  $errorMessage = null){

        $tenant = tenancy()->tenant;
        $tenantDomain = $tenant?->domain ?? $tenant?->domains?->first()?->domain;

        $data = $data+ [
            'userId' => Auth::id(),
            'tenant_id' => $tenant?->id,
            'tenant_domain' => $tenantDomain,
        ];

        Log::channel('accounting')->error($message, [$data,$errorMessage] );
    }


}