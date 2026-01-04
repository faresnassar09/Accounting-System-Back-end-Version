<?php

namespace Modules\Accounting\DTO;

class GeneralLedgerData{

public function __construct(

    public int $accountId,
    public ?string $startDate,
    public ?string $endDate,

){}

}