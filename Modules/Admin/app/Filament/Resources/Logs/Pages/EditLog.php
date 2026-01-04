<?php

namespace Modules\Admin\Filament\Resources\Logs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\Logs\LogResource;

class EditLog extends EditRecord
{
    protected static string $resource = LogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
} 
