<?php

namespace Modules\Admin\Filament\Resources\Branches;

use Modules\Branch\Models\Branch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\Branches\Pages\CreateBranch;
use Modules\Admin\Filament\Resources\Branches\Pages\EditBranch;
use Modules\Admin\Filament\Resources\Branches\Pages\ListBranches;
use Modules\Admin\Filament\Resources\Branches\Pages\ViewBranch;
use Modules\Admin\Filament\Resources\Branches\Schemas\BranchForm;
use Modules\Admin\Filament\Resources\Branches\Schemas\BranchInfolist;
use Modules\Admin\Filament\Resources\Branches\Tables\BranchesTable;

class BranchResource extends Resource
{
    protected static ?string $model = Branch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Branch';


 
    public static function form(Schema $schema): Schema
    {
        return BranchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BranchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BranchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {

        return parent::getEloquentQuery()->withCount(['admins','users']);
        
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBranches::route('/'),
            'create' => CreateBranch::route('/create'),
            'view' => ViewBranch::route('/{record}'),
            'edit' => EditBranch::route('/{record}/edit'),
        ];
    }
}
