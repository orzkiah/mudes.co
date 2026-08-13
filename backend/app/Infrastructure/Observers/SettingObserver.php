<?php

declare(strict_types=1);

namespace App\Infrastructure\Observers;

use App\Domain\Models\Setting;
use Illuminate\Support\Facades\Auth;

/**
 * Audit column population only - no business decisions
 * (IMPLEMENTATION_RULES.md §13).
 */
class SettingObserver
{
    public function creating(Setting $setting): void
    {
        $setting->created_by = Auth::id();
        $setting->updated_by = Auth::id();
    }

    public function updating(Setting $setting): void
    {
        $setting->updated_by = Auth::id();
    }

    public function deleting(Setting $setting): void
    {
        $setting->deleted_by = Auth::id();
        $setting->saveQuietly();
    }
}
