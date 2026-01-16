<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Accounting\Services\CoreAccounting\OpeningBalanceService;
use Modules\Accounting\Http\Requests\OpeningBalanceRequest;

class OpeningBalanceController extends Controller
{

    public function __construct(

        public OpeningBalanceService $openingBalanceService,

    ){} 

    public function store(OpeningBalanceRequest $data) {

        return $this->openingBalanceService->store($data);

    }

}
