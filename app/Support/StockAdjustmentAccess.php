<?php

namespace App\Support;

use App\Models\User;

final class StockAdjustmentAccess
{
    public static function allowed(?User $user): bool
    {
        // Explicit role allowlist: a general inventory permission or Gate::before
        // override must NOT accidentally grant a Staff-only user this capability.
        return $user !== null && $user->is_active && $user->hasAnyRole(['Admin', 'Doctor']);
    }

    public static function authorize(?User $user): void
    {
        abort_unless($user, 401);
        abort_unless(self::allowed($user), 403, 'Only active Admin and Doctor users can access Stock Adjustments.');
    }
}
