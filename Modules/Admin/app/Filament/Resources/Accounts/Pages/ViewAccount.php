<?php

namespace Modules\Admin\Filament\Resources\Accounts\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Modules\Admin\Filament\Resources\Accounts\AccountResource;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
