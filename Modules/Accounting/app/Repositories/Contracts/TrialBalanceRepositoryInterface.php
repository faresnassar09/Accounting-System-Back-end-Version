<?php 

namespace Modules\Accounting\Repositories\Contracts;

interface TrialBalanceRepositoryInterface{
    
    public function calculateAccountsTotals($endDate);

    
}