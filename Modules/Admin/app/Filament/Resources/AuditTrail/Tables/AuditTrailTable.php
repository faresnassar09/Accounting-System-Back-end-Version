<?php

namespace Modules\Admin\Filament\Resources\AuditTrail\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AuditTrailTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Timestamp')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),

                TextColumn::make('event')
                    ->label('Event')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created'    => 'success',
                        'updated'    => 'info',
                        'reversed'   => 'danger',
                        'deleted'    => 'danger',
                        'closed'     => 'purple',
                        'reconciled' => 'primary',
                        default      => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('auditable_type')
                    ->label('Entity')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('auditable_id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('user_name')
                    ->label('Actor / User')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('ip_address')
                    ->label('Client IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event')
                    ->options([
                        'created'    => 'Created',
                        'updated'    => 'Updated',
                        'reversed'   => 'Reversed',
                        'deleted'    => 'Deleted',
                        'closed'     => 'Closed',
                        'reconciled' => 'Reconciled',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
