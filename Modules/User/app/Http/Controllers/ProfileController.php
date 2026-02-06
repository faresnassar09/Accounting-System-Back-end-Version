<?php

namespace Modules\User\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Services\UserService;

class ProfileController extends Controller
{

    public function __construct(

        public UserService $userService,

    ) {}

          /**
   * 
   * user login 
   *
   * @group user profile
   * 
   */ 

    public function getUserData() {


       return $this->userService->getUserData();


    }
}
