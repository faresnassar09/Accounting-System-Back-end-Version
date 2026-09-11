<?php

namespace Modules\Admin\Filament\Resources\M2MLogins\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\Admin\Filament\Resources\M2MLogins\M2MLoginResource;
use Modules\Authorization\Models\Passport\ClientToken;

class ViewM2MLogin extends ViewRecord
{
    protected static string $resource = M2MLoginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('revokeToken')
                ->label('Revoke Access')
                ->icon(Heroicon::OutlinedXCircle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Revoke M2M Token')
                ->modalDescription('Are you sure you want to revoke this active access token? The external service will receive 401 Unauthorized on subsequent requests.')
                ->visible(fn (ClientToken $record) => $record->isActive())
                ->action(function (ClientToken $record) {
                    $record->revoke();

                    Notification::make()
                        ->title('Token Revoked')
                        ->body('External service token has been revoked.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
