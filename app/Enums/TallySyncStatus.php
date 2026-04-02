<?php

namespace App\Enums;

enum TallySyncStatus: string
{
    case UNSYNCED = 'UNSYNCED';
    case SYNCED   = 'SYNCED';
    case RESYNC   = 'RESYNC';

    public function label(): string
    {
        return match ($this) {
            self::UNSYNCED => 'Unsynched',
            self::SYNCED   => 'Synced',
            self::RESYNC   => 'Resync',
        };
    }
}