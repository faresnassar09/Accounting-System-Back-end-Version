<?php 

namespace Modules\Accounting\Repositories\Contracts;

interface AccountRepositoryInterface{

    public function chartTree();
    public function getAccounts();
    public function getAccount($accountId);
    public function getDebitAndCreditTotals();
    public function findRootAccount($id);
}