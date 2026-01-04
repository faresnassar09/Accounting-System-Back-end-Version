<?php

namespace Modules\Accounting\Http\Controllers\CoreAccounting;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Modules\Accounting\Http\Requests\JournalEntryRequest;
use Modules\Accounting\Services\CoreAccounting\JournalEntryService;

class JournalEntriesController extends Controller
{

    public function __construct(

        public JournalEntryService $journalEntryService
        
        ){}


    public function store(JournalEntryRequest $data) {

        
       return $this->journalEntryService->store($data);

    }

}
