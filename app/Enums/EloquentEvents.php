<?php

namespace App\Enums;

enum EloquentEvents: string
{

    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
    case RESTORED = 'restored';

    public function label(): string
    {
        return match($this) {
            self::CREATED => 'New Record Created Successfully',
            self::UPDATED => 'Record Updated Successfully',
            self::DELETED => 'Record Deleted Successfully',
        };
    }

}
