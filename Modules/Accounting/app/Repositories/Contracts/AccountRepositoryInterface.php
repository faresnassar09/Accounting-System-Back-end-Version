<?php 

namespace Modules\Accounting\Repositories\Contracts;

interface AccountRepositoryInterface{

    public function chartTree();
    public function getAllAccounts();
    public function findAccount($accountId);
    public function getClosingAccounts();
    public function findRootAccount($id);
    public function getOpeningBalanceAccount();

    
}