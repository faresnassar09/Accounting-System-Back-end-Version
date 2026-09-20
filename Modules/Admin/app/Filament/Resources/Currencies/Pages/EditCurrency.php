<?php

namespace Modules\Admin\Filament\Resources\Currencies\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Admin\Filament\Resources\Currencies\CurrencyResource;

class EditCurrency extends EditRecord
{
    protected static string $resource = CurrencyResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['code'] = strtoupper(trim($data['code']));

        if (!empty($data['is_base'])) {
            $data['exchange_rate'] = 1.000000;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->hidden(fn ($record) => (bool) $record->is_base),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
