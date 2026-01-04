<?php

namespace Modules\Admin\Filament\Resources\Admins\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\Admins\AdminResource;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
