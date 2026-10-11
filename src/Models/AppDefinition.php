<?php

namespace Novay\MiniOS\Models;

use Illuminate\Database\Eloquent\Model;

class AppDefinition extends Model
{
    protected $table = 'minios_app_definitions';

    protected $primaryKey = 'app_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['app_id', 'overrides', 'created_by', 'updated_by'];

    protected function casts(): array
    {
        return ['overrides' => 'array'];
    }
}
