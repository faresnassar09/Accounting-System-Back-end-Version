<?php

namespace Modules\Admin\Filament\Resources\Clients\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Modules\Admin\Filament\Resources\Clients\ClientResource;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            Action::make('regenerateSecret')
                ->label('Regenerate Secret')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Regenerate Client Secret')
                ->modalDescription('Are you sure you want to regenerate this secret? Any applications currently using the old secret will lose access immediately.')
                ->action(function () {
                    $newSecret = Str::random(40);
                    $this->record->forceFill(['secret' => $newSecret])->save();

                    Notification::make()
                        ->title('Secret Regenerated')
                        ->body("New Client Secret:\n`{$newSecret}`\n\nPlease copy this secret now.")
                        ->warning()
                        ->persistent()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
