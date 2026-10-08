<?php

namespace Modules\Admin\Filament\Resources\UserLogins\Tables;

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
use Modules\Authorization\Models\Passport\UserToken;

class UserLoginsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn (UserToken $record) => $record->user?->email ?? 'No email')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('status')
                    ->label('Session Status')
                    ->badge()
                    ->getStateUsing(fn (UserToken $record) => match (true) {
                        $record->revoked => 'Logged Out',
                        $record->expires_at && $record->expires_at->isPast() => 'Expired',
                        default => 'Active / Online',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Active / Online' => 'success',
                        'Logged Out' => 'gray',
                        'Expired' => 'warning',
                        default => 'secondary',
                    })
                    ->icon(fn (string $state): ?Heroicon => match ($state) {
                        'Active / Online' => Heroicon::OutlinedCheckCircle,
                        'Logged Out' => Heroicon::OutlinedArrowRightOnRectangle,
                        'Expired' => Heroicon::OutlinedClock,
                        default => null,
                    }),

                TextColumn::make('created_at')
                    ->label('Logged In At')
                    ->dateTime('Y-m-d H:i:s')
                    ->description(fn (UserToken $record) => $record->created_at?->diffForHumans())
                    ->sortable(),

                TextColumn::make('logout_time')
                    ->label('Logged Out At')
                    ->getStateUsing(function (UserToken $record) {
                        if ($record->revoked) {
                            return $record->updated_at?->format('Y-m-d H:i:s');
                        }
                        if ($record->expires_at && $record->expires_at->isPast()) {
                            return 'Expired (' . $record->expires_at?->format('Y-m-d H:i:s') . ')';
                        }
                        return 'Still Active';
                    })
                    ->description(fn (UserToken $record) => $record->revoked ? $record->updated_at?->diffForHumans() : null)
                    ->color(fn ($state) => $state === 'Still Active' ? 'success' : 'gray'),

                TextColumn::make('user.branch.name')
                    ->label('Branch')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('user.team.name')
                    ->label('Team')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Device / Client')
                    ->placeholder('API Session')
                    ->limit(20)
                    ->toggleable(),

                TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime('Y-m-d H:i:s')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'active' => 'Active / Online',
                        'logged_out' => 'Logged Out (Revoked)',
                        'expired' => 'Expired',
                    ])
                    ->query(function (Builder $query, array $data) {
                        $value = $data['value'] ?? null;
                        if ($value === 'active') {
                            $query->where('revoked', false)->where('expires_at', '>', now());
                        } elseif ($value === 'logged_out') {
                            $query->where('revoked', true);
                        } elseif ($value === 'expired') {
                            $query->where('revoked', false)->where('expires_at', '<=', now());
                        }
                    }),

                SelectFilter::make('user_id')
                    ->label('Filter By User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('revokeSelected')
                        ->label('Force Logout Selected')
                        ->icon(Heroicon::OutlinedArrowRightOnRectangle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each->revoke();
                            Notification::make()
                                ->title('Selected sessions have been revoked.')
                                ->success()
                                ->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
