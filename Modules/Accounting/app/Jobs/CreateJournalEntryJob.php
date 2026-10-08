<?php

namespace Modules\Accounting\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Accounting\Services\CoreAccounting\JournalEntryService;

class CreateJournalEntryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    public ?int $userId;

    public function __construct(array $data, ?int $userId = null) {
        $this->data = $data;
        $this->userId = $userId;
    }


    public function handle(JournalEntryService $journalEntryService): void {

        $journalEntryService->store($this->data,$this->userId);

    }
}
