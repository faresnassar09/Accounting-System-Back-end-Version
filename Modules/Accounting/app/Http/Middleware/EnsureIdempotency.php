<?php

namespace Modules\Accounting\Http\Middleware;

use App\Services\Api\ApiResponseFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EnsureIdempotency
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {

        $userIp = $request->ip();

        $idempotencyKey = $request->Idempotency_Key;
        $lockKey = "lock:journal:" . $userIp . ":{$idempotencyKey}";
        $responseKey = "response:idempotency:{$userIp}:{$idempotencyKey}";

        if ($cachedResponse = Cache::get($responseKey)) {

            return app(ApiResponseFormatter::class)->successResponse(
                'The journal entry already saved',
                [$cachedResponse],
                200
            );
        }

        $lock = Cache::lock($lockKey, 10);

        if (! $lock->get()) {

            return app(ApiResponseFormatter::class)->successResponse(
                'The journal entry already saved',
                [],
                409
            );
        }


try {
            $response = $next($request);

            if ($response->isSuccessful()) {
                Cache::put($responseKey, [
                    'body'    => json_decode($response->getContent(), true),
                    'status'  => $response->getStatusCode(),
                    'headers' => ['X-Cache-Idempotency' => 'HIT']
                ], 86400); 
            }

            return $response;

        } catch (\Throwable $e) {

            $lock->release();
            throw $e;
        }
    }
}
