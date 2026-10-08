<?php

namespace Modules\Admin\Filament\Resources\RecurringJournalEntries;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Accounting\Models\RecurringJournalEntry;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\Pages\CreateRecurringJournalEntry;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\Pages\EditRecurringJournalEntry;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\Pages\ListRecurringJournalEntries;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\Schemas\RecurringJournalEntryForm;
use Modules\Admin\Filament\Resources\RecurringJournalEntries\Tables\RecurringJournalEntriesTable;
use UnitEnum;

class RecurringJournalEntryResource extends Resource
{
    protected static ?string $model = RecurringJournalEntry::class;

    protected static string|UnitEnum|null $navigationGroup = 'Financial Accounting';

    protected static ?int $navigationSort = 6;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?string $navigationLabel = 'Recurring Entries';

    protected static ?string $recordTitleAttribute = 'reference_template';

    public static function canAccess(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('create_journal_entries', 'admin')) : false;
    }

    public static function canCreate(): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('create_journal_entries', 'admin')) : false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('create_journal_entries', 'admin')) : false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        $user = auth('admin')->user();

        return $user ? ($user->hasRole('super_admin') || $user->hasPermissionTo('create_journal_entries', 'admin')) : false;
    }

    public static function form(Schema $schema): Schema
    {
        return RecurringJournalEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecurringJournalEntriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['lines.account', 'branch', 'currency']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListRecurringJournalEntries::route('/'),
            'create' => CreateRecurringJournalEntry::route('/create'),
            'edit'   => EditRecurringJournalEntry::route('/{record}/edit'),
        ];
    }
}
