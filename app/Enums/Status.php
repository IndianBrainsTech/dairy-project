<?php

namespace App\Enums;

enum Status: string
{
    case ACTIVE     = 'ACTIVE';
    case INACTIVE   = 'INACTIVE';
    case PENDING    = 'PENDING';
    case PAUSED     = 'PAUSED';
    case ACCEPTED   = 'ACCEPTED';
    case APPROVED   = 'APPROVED';
    case GENERATED  = 'GENERATED';
    case REJECTED   = 'REJECTED';
    case CANCELLED  = 'CANCELLED';
    case PROCESSED  = 'PROCESSED';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE    => 'Active',
            self::INACTIVE  => 'Inactive',
            self::PENDING   => 'Pending',
            self::PAUSED    => 'Paused',
            self::ACCEPTED  => 'Accepted',
            self::APPROVED  => 'Approved',
            self::GENERATED => 'Generated',
            self::REJECTED  => 'Rejected',
            self::CANCELLED => 'Cancelled',
            self::PROCESSED => 'Processed',
        };
    }
}