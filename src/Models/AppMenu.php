<?php

namespace Novay\MiniOS\Models;

use Illuminate\Database\Eloquent\Model;

class AppMenu extends Model
{
    protected $table = 'minios_app_menus';

    protected $guarded = ['id'];

    protected $casts = [
        'action_payload' => 'array',
        'permissions' => 'array',
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
}
