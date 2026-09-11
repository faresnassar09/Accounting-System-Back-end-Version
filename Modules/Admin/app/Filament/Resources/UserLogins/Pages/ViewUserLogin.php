<?php

namespace Modules\Admin\Filament\Resources\UserLogins\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Modules\Admin\Filament\Resources\UserLogins\UserLoginResource;
use Modules\Authorization\Models\Passport\UserToken;

class ViewUserLogin extends ViewRecord
{
    protected static string $resource = UserLoginResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('revoke')
                ->label('Force Logout')
                ->icon(Heroicon::OutlinedArrowRightOnRectangle)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Force Logout User')
                ->modalDescription('Are you sure you want to log out this user and invalidate their session immediately?')
                ->visible(fn (UserToken $record) => $record->isActive())
                ->action(function (UserToken $record) {
                    $record->revoke();

                    Notification::make()
                        ->title('User Logged Out')
                        ->body("Session for {$record->user?->name} has been revoked.")
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
