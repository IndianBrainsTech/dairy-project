<?php

namespace App\Enums;

enum SqlAction: string
{
    case CREATE = 'CREATE';
    case INSERT = 'INSERT';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';

    public function label(): string
    {
        return match ($this) {
            self::CREATE => 'Create',
            self::INSERT => 'Insert',
            self::UPDATE => 'Update',
            self::DELETE => 'Delete',
        };
    }
}