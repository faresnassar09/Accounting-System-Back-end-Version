<?php

namespace Modules\Admin\Filament\Resources\Accounts\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\Accounts\AccountResource;

class EditAccount extends EditRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
