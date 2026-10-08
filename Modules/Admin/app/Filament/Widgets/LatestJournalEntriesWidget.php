<?php

namespace Modules\Admin\Filament\Widgets;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Modules\Accounting\Models\JournalEntry;
use Modules\Admin\Filament\Resources\JournalEntries\JournalEntryResource;

class LatestJournalEntriesWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Journal Entries';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                JournalEntry::query()
                    ->with('branch')
                    ->latest('date')
                    ->latest('id')
                    ->limit(6)
            )
            ->columns([
                TextColumn::make('reference')
                    ->label('Reference')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('date')
                    ->label('Date')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(35)
                    ->tooltip(fn ($record) => $record->description),

                TextColumn::make('branch.name')
                    ->label('Branch')
                    ->default('Consolidated')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'journal'    => 'info',
                        'opening'    => 'warning',
                        'closing'    => 'gray',
                        'adjustment' => 'purple',
                        default      => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'draft'    => 'warning',
                        'cancled'  => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('total_debit')
                    ->label('Total ($)')
                    ->money('USD')
                    ->weight('bold'),
            ])
            ->paginated(false)
            ->recordActions([
                ViewAction::make()
                    ->url(fn (JournalEntry $record): string => JournalEntryResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
