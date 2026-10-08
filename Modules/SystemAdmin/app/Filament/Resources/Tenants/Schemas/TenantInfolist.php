<?php

namespace Modules\SystemAdmin\Filament\Resources\Tenants\Schemas;

use Filament\Schemas\Schema;

class TenantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}
