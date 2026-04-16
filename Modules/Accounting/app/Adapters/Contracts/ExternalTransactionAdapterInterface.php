<?php

namespace Modules\Accounting\Adapters\Contracts;



interface ExternalTransactionAdapterInterface {


    public function transformToJournal($data);
    
}