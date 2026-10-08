<?php

namespace Modules\Admin\Filament\Resources\Currencies\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Admin\Filament\Resources\Currencies\CurrencyResource;

class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['code'] = strtoupper(trim($data['code']));

        if (!empty($data['is_base'])) {
            $data['exchange_rate'] = 1.000000;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
