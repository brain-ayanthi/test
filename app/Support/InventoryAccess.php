<?php
namespace App\Support;
use App\Models\User;
final class InventoryAccess
{
    public static function allowed(?User $user): bool
    {
        return $user !== null && $user->is_active && $user->hasAnyRole(['Admin', 'Doctor']);
    }
    public static function authorize(?User $user): void
    {
        abort_unless(self::allowed($user), 403, 'Only active Admin and Doctor users can view inventory history and reports.');
    }
}
