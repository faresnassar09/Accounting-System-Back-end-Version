<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Modules\User\Http\Requests\AuthenticationRequest;
use Modules\User\Services\AuthenticationService;
use Modules\User\Transformers\UserResource;

class AuthenticationController extends Controller
{

    public function __construct(
        public ApiResponseFormatter $apiResponse,
        public AuthenticationService $authenticationService,

    ) {}

      /**
   * 
   * user login 
   *
   * @group user auth
   * 
   */ 
    public function login(AuthenticationRequest $data)
    {

        $validatedData = $data->validated();

        if ($this->authenticationService->attempToLogin($validatedData)) {

            $user = Auth::user();

            $deviceName = request()->header('User-Agent') ?: 'Unknown Device';
            $user->tokens()->where('name', $deviceName)->delete();
            $token = $user->createToken($deviceName)->plainTextToken; 

            $user->token = $token;
            return $this->apiResponse
                ->successResponse(

                    'Logged In Successfully',
                    new UserResource($user)->additional(['token' => $token]),
                    200
                );
        }


        return $this->apiResponse
            ->failedResponse(

                'User Not Found',

                [],
            );
    }
}
