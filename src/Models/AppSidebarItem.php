<?php

namespace Novay\MiniOS\Models;

use Illuminate\Database\Eloquent\Model;

class AppSidebarItem extends Model
{
    protected $table = 'minios_app_sidebar_items';

    protected $guarded = ['id'];

    protected $casts = [
        'permissions' => 'array',
        'options' => 'array',
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
}
