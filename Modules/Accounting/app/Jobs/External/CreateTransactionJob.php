<?php

namespace Modules\Accounting\Jobs\External;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Accounting\Services\External\TransactionService;

class CreateTransactionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

     public array $data;


    public function __construct(array $data) {

        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(TransactionService $transactionService): void {


            $transactionService->create($this->data);



    }
}
