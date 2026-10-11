<?php

namespace Novay\MiniOS\Models;

use Illuminate\Database\Eloquent\Model;

class AppMenuState extends Model
{
    protected $table = 'minios_app_menu_states';

    protected $primaryKey = 'app_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];
}
