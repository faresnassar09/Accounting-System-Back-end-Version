<?php

namespace Modules\User\Services;

use App\Services\Api\ApiResponseFormatter;
use Illuminate\Support\Facades\Auth;
use Modules\User\Transformers\UserResource;

class UserService{


    public function __construct(
        public ApiResponseFormatter $apiResponse,

    ) {}


    public function getUserData(){

        $user = Auth::user();


        if ($user) {
            
            return $this->apiResponse
            ->successResponse(

                'User Information Reterived Successfully',
                new UserResource($user),
            );


        }else {

            return $this->apiResponse
            ->failedResponse(

                'User Not Found',
                []
            );


        }

    }


}