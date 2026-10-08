<?php

namespace Modules\Admin\Filament\Resources\Budgets\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\Budgets\BudgetResource;

class ListBudgets extends ListRecords
{
    protected static string $resource = BudgetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Budget Target')
                ->icon('heroicon-o-plus'),
        ];
    }
}
