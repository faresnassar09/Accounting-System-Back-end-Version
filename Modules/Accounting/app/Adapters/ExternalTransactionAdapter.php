<?php

namespace Modules\Accounting\Adapters;

use Illuminate\Support\Str;
use Modules\Accounting\Adapters\Contracts\ExternalTransactionAdapterInterface;
use Modules\Accounting\Repositories\Contracts\AccountingMappingRepositoryInterface;

class ExternalTransactionAdapter implements ExternalTransactionAdapterInterface
{

    public function __construct(

        public AccountingMappingRepositoryInterface $accountingMapping,

    ) {}


    public function transformToJournal($data)
    {

        $customersAccount = $this->accountingMapping->getCustomerAccount()?->id;
        $provierAccount =  $this->accountingMapping->getProviderAccoount()?->id;


        if ( $customersAccount === null || $customersAccount === null ){

             throw new \Exception('make sure the accounts coustomer and provider assigned in the system');
        }

        $lines = collect($data->parties['senders'])
            ->map(fn($item) => $this->mapLine($item, $customersAccount, 'debit'))
            ->concat(
                collect($data->parties['receivers'])->map(fn($item) => $this->mapLine($item, $provierAccount, 'credit'))
            );


        return [

            'header' => [

                'reference' => Str::uuid(),
                'date' => $data->timestamp,
                'description' => $data->description,
                'total_debit' => $data->total_amount,
                'total_credit' => $data->total_amount,
            ],

            'lines' => $lines,

        ];
    }


    private function mapLine($item, $accountId, $type): array
    {
        return [
            'account_id'  => $accountId,
            'debit'       =>  $type === 'debit' ? $item['amount'] : 0,
            'credit'      =>  $type === 'credit' ? $item['amount'] : 0,
            'source_reference'   =>  $item['source_reference'],
        ];
    }
}
