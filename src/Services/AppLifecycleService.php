<?php

namespace Novay\MiniOS\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Remove Window Manager metadata ONLY. Never delete business models/tables/data. */
class AppLifecycleService
{
    private const TABLES = [
        'minios_app_menus',
        'minios_app_menu_states',
        'minios_app_sidebar_items',
        'minios_app_sidebar_states',
        'minios_app_definitions',
    ];

    public function forgetConfiguration(string $appId): void
    {
        if (! preg_match('/^[a-z][a-z0-9-]{0,79}$/D', $appId)) {
            throw new \InvalidArgumentException('Invalid application identifier.');
        }
        DB::transaction(function () use ($appId) {
            foreach (self::TABLES as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->where('app_id', $appId)->delete();
                }
            }
        });
        app(AppDefinitionResolver::class)->forget();
    }
}
