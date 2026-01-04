<?php

namespace Modules\Admin\Filament\Resources\Teams;

use Modules\Teams\Models\Team;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Admin\Filament\Resources\Teams\Pages\CreateTeam;
use Modules\Admin\Filament\Resources\Teams\Pages\EditTeam;
use Modules\Admin\Filament\Resources\Teams\Pages\ListTeams;
use Modules\Admin\Filament\Resources\Teams\Pages\ViewTeam;
use Modules\Admin\Filament\Resources\Teams\Schemas\TeamForm;
use Modules\Admin\Filament\Resources\Teams\Schemas\TeamInfolist;
use Modules\Admin\Filament\Resources\Teams\Tables\TeamsTable;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Modules\Teams\Models\Team';

    public static function form(Schema $schema): Schema
    {
        return TeamForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TeamInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TeamsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        
        return parent::getEloquentQuery()->withCount('users');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeams::route('/'),
            'create' => CreateTeam::route('/create'),
            'view' => ViewTeam::route('/{record}'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }
}
