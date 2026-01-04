<?php

namespace Modules\Accounting\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;


class AccountantController extends Controller
{
 
    public function dashboard(){

        return Inertia::render('Accounting::Dashboard');
        
    }
}
