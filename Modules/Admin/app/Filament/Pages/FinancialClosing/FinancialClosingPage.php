<?php

namespace Modules\Admin\Filament\Pages\FinancialClosing;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Modules\Accounting\Models\Account;
use Modules\Accounting\Models\ClosedFinancialYear;
use Modules\Accounting\Repositories\Contracts\FinancialClosingReposiroryInterface;
use Modules\Accounting\Services\CoreAccounting\FinancialClosingService;
use UnitEnum;

class FinancialClosingPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Financial Accounting';

    protected static ?int $navigationSort = 4;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-lock-closed';

    protected static ?string $navigationLabel = 'Financial Year Closing';

    protected static ?string $title = 'Financial Year-End Closing & Roll-Forward';

    protected static ?string $slug = 'financial-closing';

    protected string $view = 'admin::filament.pages.financial-closing';

    public int $selectedYear;

    public ?int $retainedEarningsAccountId = null;

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('manage_financial_closing', 'admin')) : false;
    }

    public function mount(): void
    {
        $this->selectedYear = (int) now()->format('Y');

        $retainedEarningsAccount = Account::query()
            ->whereHas('accountType', fn ($q) => $q->where('type', 'retained_earnings'))
            ->first();

        if ($retainedEarningsAccount) {
            $this->retainedEarningsAccountId = $retainedEarningsAccount->id;
        } else {
            $fallbackEquity = Account::query()
                ->whereHas('accountType', fn ($q) => $q->where('account_group', 'equity'))
                ->first();
            $this->retainedEarningsAccountId = $fallbackEquity?->id;
        }
    }

    public function getAvailableYearsProperty(): array
    {
        $currentYear = (int) now()->format('Y');
        $years = [];
        for ($y = $currentYear; $y >= $currentYear - 6; $y--) {
            $years[$y] = (string) $y;
        }
        return $years;
    }

    public function getEquityAccountsProperty()
    {
        return Account::query()
            ->whereHas('accountType', fn ($q) => $q->where('account_group', 'equity'))
            ->orderBy('number')
            ->get()
            ->mapWithKeys(fn ($acc) => [
                $acc->id => "#{$acc->number} — {$acc->name}",
            ]);
    }

    public function getPnlPreviewProperty(): array
    {
        try {
            return app(FinancialClosingService::class)->getRevenuesAndExpenses($this->selectedYear);
        } catch (\Throwable $e) {
            return [
                'total_revenues' => 0.00,
                'total_expenses' => 0.00,
                'net_profit'     => 0.00,
            ];
        }
    }

    public function getIsClosedProperty(): bool
    {
        return (bool) app(FinancialClosingReposiroryInterface::class)->isYearClosed($this->selectedYear);
    }

    public function getClosedYearsProperty()
    {
        return ClosedFinancialYear::query()
            ->orderBy('year', 'desc')
            ->get();
    }

    public function applyClosing(): void
    {
        if ($this->isClosed) {
            Notification::make()
                ->title('Year Already Closed')
                ->body("Financial Year ({$this->selectedYear}) is already locked and closed.")
                ->danger()
                ->send();
            return;
        }

        if (! $this->retainedEarningsAccountId) {
            Notification::make()
                ->title('Missing Destination Account')
                ->body('Please select a Retained Earnings / Equity destination account before closing the year.')
                ->danger()
                ->send();
            return;
        }

        try {
            app(FinancialClosingService::class)->applyClosingFinancialYear([
                'year'       => $this->selectedYear,
                'account_id' => $this->retainedEarningsAccountId,
            ]);

            Notification::make()
                ->title('Financial Year Successfully Closed!')
                ->body("Fiscal Year {$this->selectedYear} has been closed. Nominal accounts reset to zero, net income rolled forward to Retained Earnings, and next year's opening balances posted.")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Closing Failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
