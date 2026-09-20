<?php

namespace Modules\Admin\Filament\Resources\FixedAssets\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\FixedAssets\FixedAssetResource;

class CreateFixedAsset extends CreateRecord
{
    protected static string $resource = FixedAssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $cost = (float) ($data['purchase_cost'] ?? 0.00);
        $data['accumulated_depreciation'] = 0.00;
        $data['book_value'] = $cost;
        $data['status'] = 'active';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
