<?php

namespace Modules\Admin\Filament\Resources\Roles\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Admin\Filament\Resources\Roles\RoleResource;
use Modules\Authorization\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),

            Action::make('editPermissions')
            ->label('Edit Permissions')
            ->icon('heroicon-o-key')
            ->form([
                CheckboxList::make('permissions')
                    ->label('Select Permissions')
                    ->columns(2)
                    ->searchable() 
                    
                    ->options(function (Model $record): array {
                        
                        $allPermissions = Permission::all();
                        
                        $grantedIds = $record->permissions->pluck('id')->toArray();
                        
                        $granted = $allPermissions->whereIn('id', $grantedIds);
                        $other = $allPermissions->whereNotIn('id', $grantedIds);
                        
                        return $granted->merge($other)
                                       ->pluck('name', 'id')
                                       ->toArray();
                    })
            ])
            ->fillForm(function (Model $record): array {

                $permissionIds = $record->permissions()->pluck('permission_id')->toArray();
                return [
'permissions' => $permissionIds,

];
            })
            ->action(function (Model $record, array $data): void {
                $record->permissions()->sync($data['permissions']);

                Notification::make()
                    ->title('Permissions Updated Successfully !')
                    ->success()
                    ->send();
            }),
        ];
    }
}
