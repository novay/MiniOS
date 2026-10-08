<?php

namespace Novay\MiniOS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Novay\MiniOS\MiniOS register(\Novay\MiniOS\Contracts\DesktopApp|string|array $app)
 * @method static array getApplications()
 * @method static \Novay\MiniOS\Contracts\DesktopApp|null getApplication(string $id)
 * @method static \Novay\MiniOS\Support\AppRegistry registry()
 * @method static void routes()
 * @method static void fortify()
 *
 * @see \Novay\MiniOS\MiniOS
 */
class MiniOS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'novay.minios';
    }
}
