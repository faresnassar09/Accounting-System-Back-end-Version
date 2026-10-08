<?php

namespace Modules\Admin\Filament\Resources\FixedAssets\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Admin\Filament\Resources\FixedAssets\FixedAssetResource;

class ListFixedAssets extends ListRecords
{
    protected static string $resource = FixedAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Asset Register')
                ->icon('heroicon-o-plus'),
        ];
    }
}
