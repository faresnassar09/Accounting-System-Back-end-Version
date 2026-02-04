<?php

namespace Modules\Accounting\Observers;

use App\Enums\EloquentEvents;
use App\Services\Logging\ActivityService;
use Modules\Accounting\Models\Account;

class AccountObserver
{


        
 
    public function __construct(public ActivityService $activityService){}
    
    public function created(Account $account): void {

          $this->activityService->log(
            
            effected: $account,
            event:    EloquentEvents::CREATED,
            message:  EloquentEvents::CREATED->label(),
            data:     [],
        );

        if ($account->account_id) {
            $account->recalculateParentBalances();
            $account->recalculateDescendantsCount();
        }


    }


    


    /**
     * Handle the Account "updated" event.
     */
    public function updated(Account $account): void {


        if ($account->isDirty()) {

            $this->activityService->log(
            
                effected: $account,
                event:    EloquentEvents::UPDATED,
                message:  EloquentEvents::UPDATED->label(),
                data:     [],
            );
        }

    }

public function deleting(Account $account){

    if ($account->entryLines()->exists()) {
        \Filament\Notifications\Notification::make()
        ->title('فشل الحذف')
        ->body('لا يمكن حذف حساب مرتبط بحركات مالية.')
        ->danger()
        ->persistent() // عشان الرسالة متختفيش بسرعة
        ->send();
        
        return false; 
    }

}
    public function deleted(Account $account): void {

        $this->activityService->log(
            
            effected: $account,
            event:    EloquentEvents::DELETED,
            message:  EloquentEvents::DELETED->label(),
            data:     [],
        );
   
    }
 
        public function creating(Account $account): void
        {

        }

}
