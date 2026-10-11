<?php

namespace Novay\MiniOS\Models;

use Illuminate\Database\Eloquent\Model;

class AppSidebarState extends Model
{
    protected $table = 'minios_app_sidebar_states';

    protected $primaryKey = 'app_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['app_id'];
}
