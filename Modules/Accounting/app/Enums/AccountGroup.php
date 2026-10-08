<?php

namespace Modules\Accounting\Enums;

enum  AccountGroup: string{

    case REVENUES = 'revenues';
    case EXPENSES = 'expenses';
    case ASSETS = 'assets';
    case EQUITY = 'equity';
    case LIABILITIES ='liabilities';

}
