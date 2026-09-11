<?php

namespace Modules\Admin\Filament\Resources\M2MLogins\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Authorization\Models\Passport\ClientToken;

class M2MLoginsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('External Service / Client')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->placeholder('Unknown Client'),

                TextColumn::make('client_id')
                    ->label('Client ID')
                    ->copyable()
                    ->copyMessage('Client ID copied to clipboard')
                    ->fontFamily('mono')
                    ->limit(16)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Token Status')
                    ->badge()
                    ->getStateUsing(fn (ClientToken $record) => match (true) {
                        $record->revoked => 'Revoked',
                        $record->expires_at && $record->expires_at->isPast() => 'Expired',
                        default => 'Active Token',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Active Token' => 'success',
                        'Revoked' => 'danger',
                        'Expired' => 'warning',
                        default => 'secondary',
                    })
                    ->icon(fn (string $state): ?Heroicon => match ($state) {
                        'Active Token' => Heroicon::OutlinedCheckCircle,
                        'Revoked' => Heroicon::OutlinedXCircle,
                        'Expired' => Heroicon::OutlinedClock,
                        default => null,
                    }),

                TextColumn::make('created_at')
                    ->label('Authenticated At')
                    ->dateTime('Y-m-d H:i:s')
                    ->description(fn (ClientToken $record) => $record->created_at?->diffForHumans())
                    ->sortable(),

                TextColumn::make('revoked_at')
                    ->label('Revoked / Closed At')
                    ->getStateUsing(function (ClientToken $record) {
                        if ($record->revoked) {
                            return $record->updated_at?->format('Y-m-d H:i:s');
                        }
                        if ($record->expires_at && $record->expires_at->isPast()) {
                            return 'Expired (' . $record->expires_at?->format('Y-m-d H:i:s') . ')';
                        }
                        return 'Active';
                    })
                    ->description(fn (ClientToken $record) => $record->revoked ? $record->updated_at?->diffForHumans() : null)
                    ->color(fn ($state) => $state === 'Active' ? 'success' : 'gray'),

                TextColumn::make('scopes')
                    ->label('Scopes')
                    ->badge()
                    ->placeholder('Full Access (*)')
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active Tokens',
                        'revoked' => 'Revoked Tokens',
                        'expired' => 'Expired Tokens',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value === 'active') {
                            $query->where('revoked', false)->where('expires_at', '>', now());
                        } elseif ($value === 'revoked') {
                            $query->where('revoked', true);
                        } elseif ($value === 'expired') {
                            $query->where('revoked', false)->where('expires_at', '<=', now());
                        }
                    }),

                SelectFilter::make('client_id')
                    ->label('Filter By Client')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('revokeSelected')
                        ->label('Revoke Selected Tokens')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->revoke();
                            Notification::make()
                                ->title('Selected tokens have been revoked.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
