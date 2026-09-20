<?php

namespace Modules\Admin\Filament\Resources\BankReconciliations\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\BankReconciliations\BankStatementResource;

class ListBankStatements extends ListRecords
{
    protected static string $resource = BankStatementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Upload Bank Statement')
                ->icon('heroicon-o-plus'),
        ];
    }
}
