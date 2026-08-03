<?php

namespace App\Enums;

enum MembershipIdentifierType: string
{
    case MembershipId = 'membership_id';
    case Email = 'email';
    case StudentId = 'student_id';

    public function label(): string
    {
        return match ($this) {
            self::MembershipId => 'Membership ID',
            self::Email => 'Email ID',
            self::StudentId => 'Student ID',
        };
    }

    public function listLabel(): string
    {
        return match ($this) {
            self::MembershipId => 'Membership IDs',
            self::Email => 'Email IDs',
            self::StudentId => 'Student IDs',
        };
    }

    public function placeholder(): string
    {
        return match ($this) {
            self::MembershipId => 'MEM-12345',
            self::Email => 'member@example.com',
            self::StudentId => 'STU-998877',
        };
    }
}
