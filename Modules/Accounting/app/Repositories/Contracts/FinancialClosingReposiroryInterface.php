<?php

namespace Modules\Accounting\Repositories\Contracts;


interface FinancialClosingReposiroryInterface {

public function isYearClosed($year);
public function flagYearAsClosed($year,$netProfit,$clogingAccountId);
}