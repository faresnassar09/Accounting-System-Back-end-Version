<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Api\ApiResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Modules\User\Http\Requests\AuthenticationRequest;
use Modules\User\Http\Requests\LogoutRequest;
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

$token = $user->createToken('Token Name')->accessToken;

            return $this->apiResponse
                ->successResponse(

                    'Logged In Successfully',
                    new UserResource($user,$token),
                    200
                );
        }


        return $this->apiResponse
            ->failedResponse(

                'User Not Found',

                [],
            );
    }

    public function logout(LogoutRequest $request){


 $request->user()->token()->revoke();

    }
}
