<?php

namespace Modules\Admin\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Modules\Branch\Models\Branch;
use Modules\Teams\Models\Team;
use Spatie\Permission\Models\Role;

use function Livewire\Volt\rules;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                
                TextInput::make('name')
                    ->rules(['required','min:5','max:255']),

                TextInput::make('email')
                    ->rules(['required','email'])
                    ->unique(
                        ignoreRecord: true,
                    )
                    ->label('Email address')
                    ->email(),

                TextInput::make('password')
                    ->rules(['required','min:8','max:32'])
                    ->hiddenOn('edit'),

                    Select::make('branch_id')
                    ->label('Branch')
                    ->searchable()
                    ->options(Branch::query()->pluck('name','id'))
                    ->visible(fn() => current_guard_user()->hasRole('super_admin')),

                    Select::make('team_id')
                    ->required()
                    ->label('Team')
                    ->searchable()
                    ->options(Team::query()->pluck('name','id')),

                    Select::make('role')
                    ->searchable()
                    ->rules(['required','exists:roles,id'])
                    ->options(Role::where('guard_name','web')->pluck('name','id'))
                    ->dehydrated(false),
                    
                    FileUpload::make('profile_photo_path')
                    ->disk('public')
                    ->directory('users_avatars')
                    ->image()
                    ->maxSize(1024)
                    ->default(null)
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper(),
                
                ]);
    }
   

}
