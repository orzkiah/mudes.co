<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    /**
     * Spatie Permission caches roles/permissions in the app's default cache
     * store (Redis here), which RefreshDatabase's transaction rollback does
     * not touch - without this, a role's permission set cached by an
     * earlier test can leak into a later test that reassigns the same role
     * name different permissions.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
