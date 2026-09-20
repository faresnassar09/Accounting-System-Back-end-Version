<?php

namespace Modules\Admin\Filament\Resources\JournalEntries;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Accounting\Models\JournalEntry;
use Modules\Admin\Filament\Resources\JournalEntries\Pages\CreateJournalEntry;
use Modules\Admin\Filament\Resources\JournalEntries\Pages\ListJournalEntries;
use Modules\Admin\Filament\Resources\JournalEntries\Pages\ViewJournalEntry;
use Modules\Admin\Filament\Resources\JournalEntries\Schemas\JournalEntryForm;
use Modules\Admin\Filament\Resources\JournalEntries\Schemas\JournalEntryInfolist;
use Modules\Admin\Filament\Resources\JournalEntries\Tables\JournalEntriesTable;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'reference';

    public static function form(Schema $schema): Schema
    {
        return JournalEntryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return JournalEntryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JournalEntriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['lines.account']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListJournalEntries::route('/'),
            'create' => CreateJournalEntry::route('/create'),
            'view'   => ViewJournalEntry::route('/{record}'),
        ];
    }
}
