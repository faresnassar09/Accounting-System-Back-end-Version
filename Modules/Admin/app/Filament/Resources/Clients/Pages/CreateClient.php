<?php

namespace Modules\Admin\Filament\Resources\Clients\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Modules\Admin\Filament\Resources\Clients\ClientResource;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['secret'] = !empty($data['secret']) ? $data['secret'] : Str::random(40);
        $data['provider'] = 'users';
        $data['revoked'] = false;
        $data['redirect_uris'] = !empty($data['redirect_uris']) ? (array) $data['redirect_uris'] : [];
        $data['grant_types'] = !empty($data['grant_types']) ? (array) $data['grant_types'] : ['client_credentials'];

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('OAuth Client Created')
            ->body("Client ID: `{$this->record->id}`\n\nClient Secret: `{$this->record->secret}`\n\n⚠️ Please copy and store this secret securely.")
            ->persistent()
            ->warning()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
