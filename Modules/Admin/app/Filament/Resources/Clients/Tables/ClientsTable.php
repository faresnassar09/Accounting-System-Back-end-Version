<?php

namespace Modules\Admin\Filament\Resources\Clients\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Authorization\Models\Passport\Client;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Client Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('id')
                    ->label('Client ID')
                    ->formatStateUsing(fn ($state) => $state ? substr($state, 0, 8) . '••••••••' : '—')
                    ->copyable()
                    ->copyableState(fn ($record) => $record->id)
                    ->copyMessage('Client Secret copied to clipboard')
                    ->fontFamily('mono'),

                TextColumn::make('grant_types')
                    ->label('Grant Types')
                    ->badge()
                    ->separator(','),

                IconColumn::make('revoked')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckCircle)
                    ->falseIcon(Heroicon::OutlinedXCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn ($record) => !$record->revoked),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('regenerateSecret')
                    ->label('Regenerate Secret')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Regenerate Client Secret')
                    ->modalDescription('Are you sure you want to regenerate this secret? Any applications currently using this secret will lose access immediately.')
                    ->action(function (Client $record) {
                        $newSecret = Str::random(40);
                        $record->forceFill(['secret' => $newSecret])->save();

                        Notification::make()
                            ->title('Secret Regenerated')
                            ->body("New Client Secret Generated Place Make Sure to Copy it because it is one time show \n\n ({$newSecret})")
                            ->warning()
                            ->persistent()
                            ->send();
                    }),
                Action::make('toggleRevoke')
                    ->label(fn (Client $record) => $record->revoked ? 'Activate' : 'Revoke')
                    ->icon(fn (Client $record) => $record->revoked ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle)
                    ->color(fn (Client $record) => $record->revoked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->action(fn (Client $record) => $record->forceFill(['revoked' => !$record->revoked])->save()),
                    DeleteAction::make(),
            ])
        
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
