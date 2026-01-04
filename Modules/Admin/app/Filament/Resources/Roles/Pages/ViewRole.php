<?php

namespace Modules\Admin\Filament\Resources\Roles\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Filament\Resources\Roles\RoleResource;


class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

 
        ];
    }
}
